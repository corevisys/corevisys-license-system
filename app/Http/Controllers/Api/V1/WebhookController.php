<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProcessedWebhook;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

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
            \Illuminate\Support\Facades\Log::error("Stripe Webhook Secret missing in settings.");
            return response()->json(['message' => 'Config Error'], 500);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['message' => 'Invalid Payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            \Illuminate\Support\Facades\Log::warning("Stripe Webhook Signature Verification Failed", [
                'ip' => $request->ip(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        // 2. Idempotency Check
        $eventId = $event->id;
        if (ProcessedWebhook::where('event_id', $eventId)->exists()) {
            return response()->json(['message' => 'Already Processed'], 200);
        }

        // 3. Process Events
        return \Illuminate\Support\Facades\DB::transaction(function () use ($event, $gateway, $eventId) {
            $processedWebhook = ProcessedWebhook::create([
                'gateway' => $gateway,
                'event_id' => $eventId,
                'payload' => $event->toArray(),
                'processed_at' => now(),
            ]);

            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    $this->fulfillOrder($session);
                    break;
                case 'invoice.paid':
                    $invoice = $event->data->object;
                    $this->markSubscriptionPaid($invoice);
                    \Illuminate\Support\Facades\Log::info("Stripe Webhook: Invoice Paid", ['invoice_id' => $invoice->id]);
                    break;
                case 'invoice.payment_failed':
                    $invoice = $event->data->object;
                    $this->markSubscriptionPaymentFailed($invoice);
                    \Illuminate\Support\Facades\Log::warning("Stripe Webhook: Invoice Payment Failed", ['invoice_id' => $invoice->id]);
                    break;
                case 'customer.subscription.deleted':
                    $subscription = $event->data->object;
                    $license = \App\Models\License::where('gateway_subscription_id', $subscription->id)->first();
                    if ($license) {
                        $license->update(['auto_renew' => false, 'status' => 'expired']);
                        $this->notifyCustomer($license, 'your Stripe subscription was cancelled and the license has been set to expired');
                    }
                    \Illuminate\Support\Facades\Log::info("Stripe Webhook: Subscription Deleted", ['sub_id' => $subscription->id]);
                    break;
                case 'payment_intent.succeeded':
                    // handled via checkout session usually, but good to have
                    break;
                default:
                    \Illuminate\Support\Facades\Log::info("Unhandled Stripe Webhook Event: " . $event->type);
            }

            return response()->json(['message' => 'Processed'], 200);
        });
    }

    protected function markSubscriptionPaid($invoice): void
    {
        $subscriptionId = $invoice->subscription ?? null;
        $license = $subscriptionId
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

        $base = $license->expires_at ?? now();
        $newExpiry = $base->copy()->addDays($billingPeriod);

        $license->update([
            'expires_at' => $newExpiry,
            'next_billing_at' => $newExpiry->copy()->addDays($billingPeriod),
            'grace_expires_at' => null,
            'status' => 'active',
            'auto_renew' => true,
            'last_check_at' => now(),
        ]);

        $invoiceId = $invoice->id ?? null;
        $paymentIntent = $invoice->payment_intent ?? null;

        if ($invoiceId || $paymentIntent) {
            $paymentQuery = \App\Models\Payment::query();
            if ($invoiceId) {
                $paymentQuery->where('transaction_id', $invoiceId);
            }
            if ($paymentIntent) {
                $paymentQuery->orWhere('transaction_id', $paymentIntent);
            }

            $paymentQuery->where('order_id', $license->order_id)->update(['status' => 'verified']);
        }
    }

    protected function markSubscriptionPaymentFailed($invoice): void
    {
        $subscriptionId = $invoice->subscription ?? null;
        $license = $subscriptionId
            ? \App\Models\License::where('gateway_subscription_id', $subscriptionId)->first()
            : null;

        if ($license) {
            $license->update([
                'grace_expires_at' => $license->grace_expires_at ?: now()->addDays(7),
            ]);
            $this->notifyCustomer($license, 'your recurring payment failed and grace period protection has been started');
        }

        $paymentIntent = $invoice->payment_intent ?? null;
        if ($paymentIntent) {
            \App\Models\Payment::where('transaction_id', $paymentIntent)
                ->where('status', 'pending')
                ->update(['status' => 'failed']);
        }
    }

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
            \Illuminate\Support\Facades\Log::error('Subscription notification failed', [
                'license_id' => $license->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function fulfillOrder($session)
    {
        $orderId = $session->metadata->order_id ?? $session->client_reference_id;
        
        if (!$orderId) {
            \Illuminate\Support\Facades\Log::warning("Stripe Webhook: Order ID missing.", ['session' => $session->id]);
            return;
        }

        $order = \App\Models\Order::with('payment')->find($orderId);

        if (!$order) {
            \Illuminate\Support\Facades\Log::error("Stripe Webhook: Order not found.", ['order_id' => $orderId]);
            return;
        }

        // Use OrderFulfillmentService. Re-throw on failure so the DB transaction
        // rolls back and Stripe retries the webhook (instead of silently acking).
        $fulfillmentService = app(\App\Services\OrderFulfillmentService::class);
        $fulfillmentService->fulfillOrder($order, [
            'transaction_id' => $session->payment_intent ?? $session->id,
            'gateway_response' => $session->toArray()
        ]);
    }
}
