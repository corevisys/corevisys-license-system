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
                case 'invoice.payment_failed':
                    $invoice = $event->data->object;
                    // Handle payment failure (e.g., notify user, marking order as failed if needed)
                    \Illuminate\Support\Facades\Log::warning("Stripe Webhook: Invoice Payment Failed", ['invoice_id' => $invoice->id]);
                    break;
                case 'customer.subscription.deleted':
                    $subscription = $event->data->object;
                    // Handle subscription cancellation
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

        // Use OrderFulfillmentService
        try {
            $fulfillmentService = app(\App\Services\OrderFulfillmentService::class);
            $fulfillmentService->fulfillOrder($order, [
                'transaction_id' => $session->payment_intent ?? $session->id,
                'gateway_response' => $session->toArray()
            ]);
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error("Stripe Webhook: Fulfillment Error", ['order_id' => $orderId, 'error' => $e->getMessage()]);
        }
    }
}
