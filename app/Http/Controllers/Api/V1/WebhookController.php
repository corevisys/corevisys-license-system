<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProcessedWebhook;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request, string $gateway)
    {
        if ($gateway !== 'stripe') {
            return response()->json(['message' => 'Unsupported gateway'], 400);
        }

        $stripeService = new \App\Services\StripePaymentService();
        $webhookSecret = $stripeService->getWebhookSecret();

        if (!$webhookSecret) {
            Log::error("Stripe Webhook Secret missing in settings.");
            return response()->json(['message' => 'Config Error'], 500);
        }

        $payload    = $request->getContent();
        $sigHeader  = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['message' => 'Invalid Payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning("Stripe Webhook Signature Verification Failed", [
                'ip'    => $request->ip(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        $eventId = $event->id ?? null;

        // ---------------------------------------------------------------------------
        // Validation: event_id integrity check before idempotency insert
        //
        // WHY this validation is required:
        //   On MySQL, INSERT IGNORE does not fail or return 0 rows for data truncation
        //   or NOT NULL violations; instead, it coerces a NOT NULL violation into an
        //   implicit default (empty string '') and silently truncates strings exceeding
        //   the column length (VARCHAR(255)).
        //   Because MySQL inserts these corrupted values with rowCount = 1, the post-insert
        //   existence check cannot detect the anomaly.
        //   Therefore, we validate that $eventId is a non-empty string and does not exceed
        //   the 255-character column limit before attempting any database operation.
        //   If invalid, we return the pre-remediation invalid-payload error (400 Invalid Payload)
        //   without writing to processed_webhooks or reporting "Already Processed".
        // ---------------------------------------------------------------------------
        if (!is_string($eventId) || $eventId === '' || strlen($eventId) > 255) {
            Log::warning("Stripe Webhook received with invalid or over-long event_id", [
                'event_id' => is_string($eventId) ? substr($eventId, 0, 32) . '...' : null,
                'gateway'  => $gateway,
            ]);
            return response()->json(['message' => 'Invalid Payload'], 400);
        }

        // ---------------------------------------------------------------------------
        // Idempotency strategy: insertOrIgnore + existence verification
        //
        // WHY insertOrIgnore, not try/catch(UniqueConstraintViolationException)?
        //   Catching a unique violation *outside* the transaction would also swallow
        //   unique violations raised by the business logic (e.g. fulfillOrder inserting
        //   a duplicate payment row), silently reporting "Already Processed" and
        //   preventing the gateway from retrying.
        //
        // WHY the post-insert existence check?
        //   On MySQL, INSERT IGNORE suppresses ALL constraint errors (NOT NULL,
        //   truncation, foreign-key) and returns 0 rows affected. Without the check,
        //   a mis-configured event with a NULL event_id would be silently reported as
        //   "Already Processed" when in fact no row was ever written and no handler ran.
        //
        // Concurrency (TOCTOU):
        //   Both the winning and the losing concurrent requests read 1 or 0 from
        //   insertOrIgnore atomically. The loser then reads the row that the winner
        //   committed. There is no window between the check and the read.
        // ---------------------------------------------------------------------------

        return DB::transaction(function () use ($event, $gateway, $eventId, $payload) {
            $insertedCount = DB::table('processed_webhooks')->insertOrIgnore([
                'gateway'      => $gateway,
                'event_id'     => $eventId,
                'payload'      => json_encode($event->toArray()),
                'processed_at' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            if ($insertedCount === 0) {
                // Either a true duplicate OR a suppressed constraint error.
                // Verify which case we are in by checking whether the row exists.
                $rowExists = DB::table('processed_webhooks')
                    ->where('gateway', $gateway)
                    ->where('event_id', $eventId)
                    ->exists();

                if (!$rowExists) {
                    // The insert was suppressed for a non-duplicate reason
                    // (e.g. NULL event_id). Throw so the gateway retries and
                    // so our logs capture the anomaly.
                    throw new \RuntimeException(
                        "insertOrIgnore returned 0 rows but no matching processed_webhooks row exists "
                        . "for gateway={$gateway} event_id=" . ($eventId ?? 'NULL')
                        . ". The insert was silently suppressed by the database."
                    );
                }

                // True duplicate — already processed.
                return response()->json(['message' => 'Already Processed'], 200);
            }

            // Row inserted. Business logic runs inside the same transaction.
            // Any exception here (unique violations on other tables, FK errors, etc.)
            // propagates unmodified: the transaction rolls back the newly inserted
            // processed_webhooks row, and the gateway retries the event.
            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->fulfillOrder($event->data->object);
                    break;

                case 'invoice.paid':
                    $this->markSubscriptionPaid($event->data->object);
                    Log::info("Stripe Webhook: Invoice Paid", [
                        'invoice_id' => $event->data->object->id,
                    ]);
                    break;

                case 'invoice.payment_failed':
                    $this->markSubscriptionPaymentFailed($event->data->object);
                    Log::warning("Stripe Webhook: Invoice Payment Failed", [
                        'invoice_id' => $event->data->object->id,
                    ]);
                    break;

                case 'customer.subscription.deleted':
                    $subscription = $event->data->object;
                    $license = \App\Models\License::where('gateway_subscription_id', $subscription->id)->first();
                    if ($license) {
                        $license->update(['auto_renew' => false, 'status' => 'expired']);
                        // notifyCustomer is fire-and-forget (mail failure must not
                        // roll back the license state change already queued).
                        $this->notifyCustomer(
                            $license,
                            'your Stripe subscription was cancelled and the license has been set to expired'
                        );
                    }
                    Log::info("Stripe Webhook: Subscription Deleted", [
                        'sub_id' => $subscription->id,
                    ]);
                    break;

                case 'payment_intent.succeeded':
                    // Normally handled via checkout.session.completed; no-op here.
                    break;

                default:
                    Log::info("Unhandled Stripe Webhook Event: " . $event->type);
            }

            return response()->json(['message' => 'Processed'], 200);
        });
    }

    protected function markSubscriptionPaid($invoice): void
    {
        $subscriptionId = $invoice->subscription ?? null;
        $license        = $subscriptionId
            ? \App\Models\License::where('gateway_subscription_id', $subscriptionId)->first()
            : null;

        if (!$license) {
            return;
        }

        $billingPeriod = 30;
        $price = $license->product?->prices()->where('type', 'subscription')->first();
        if ($price && $price->billing_period) {
            $billingPeriod = (int) $price->billing_period;
        }

        $base      = $license->expires_at ?? now();
        $newExpiry = $base->copy()->addDays($billingPeriod);

        $license->update([
            'expires_at'       => $newExpiry,
            'next_billing_at'  => $newExpiry->copy()->addDays($billingPeriod),
            'grace_expires_at' => null,
            'status'           => 'active',
            'auto_renew'       => true,
            'last_check_at'    => now(),
        ]);

        $invoiceId     = $invoice->id ?? null;
        $paymentIntent = $invoice->payment_intent ?? null;

        if ($invoiceId || $paymentIntent) {
            $q = \App\Models\Payment::query();
            if ($invoiceId) {
                $q->where('transaction_id', $invoiceId);
            }
            if ($paymentIntent) {
                $q->orWhere('transaction_id', $paymentIntent);
            }
            $q->where('order_id', $license->order_id)->update(['status' => 'verified']);
        }
    }

    protected function markSubscriptionPaymentFailed($invoice): void
    {
        $subscriptionId = $invoice->subscription ?? null;
        $license        = $subscriptionId
            ? \App\Models\License::where('gateway_subscription_id', $subscriptionId)->first()
            : null;

        if ($license) {
            $license->update([
                'grace_expires_at' => $license->grace_expires_at ?: now()->addDays(7),
            ]);
            $this->notifyCustomer(
                $license,
                'your recurring payment failed and grace period protection has been started'
            );
        }

        $paymentIntent = $invoice->payment_intent ?? null;
        if ($paymentIntent) {
            \App\Models\Payment::where('transaction_id', $paymentIntent)
                ->where('status', 'pending')
                ->update(['status' => 'failed']);
        }
    }

    /**
     * Fire-and-forget mail notification.
     *
     * This intentionally catches mail exceptions so that a transient SMTP
     * failure does not roll back the surrounding DB transaction (e.g. a
     * payment-failed record that has already been committed).  It does NOT
     * catch database or other infrastructure exceptions.
     */
    protected function notifyCustomer(\App\Models\License $license, string $reason): void
    {
        $user = $license->user;
        if (!$user) {
            return;
        }

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Your CoreVisys subscription requires attention: {$reason}.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('CoreVisys Subscription Notice');
                }
            );
        } catch (\Throwable $e) {
            Log::error('Subscription notification failed', [
                'license_id' => $license->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    protected function fulfillOrder($session): void
    {
        $orderId = $session->metadata->order_id ?? $session->client_reference_id;

        if (!$orderId) {
            Log::warning("Stripe Webhook: Order ID missing.", ['session' => $session->id]);
            return;
        }

        $order = \App\Models\Order::with('payment')->find($orderId);

        if (!$order) {
            Log::error("Stripe Webhook: Order not found.", ['order_id' => $orderId]);
            return;
        }

        // Exceptions from fulfillOrder propagate into the DB::transaction callback,
        // rolling back the processed_webhooks insert so Stripe retries.
        $fulfillmentService = app(\App\Services\OrderFulfillmentService::class);
        $fulfillmentService->fulfillOrder($order, [
            'transaction_id'   => $session->payment_intent ?? $session->id,
            'gateway_response' => $session->toArray(),
        ]);
    }
}
