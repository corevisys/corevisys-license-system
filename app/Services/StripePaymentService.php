<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductPrice;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePaymentService
{
    public function __construct()
    {
        $this->initStripe();
    }

    protected function initStripe()
    {
        $secretKey = SystemSetting::where('key', 'gateway_stripe_secret')->value('value');
        if (!$secretKey) {
            Log::error('Stripe Secret Key not found in SystemSettings.');
            return;
        }
        Stripe::setApiKey($secretKey);
    }

    /**
     * Create a Stripe Checkout Session for an Order.
     */
    public function createCheckoutSession(Order $order, ProductPrice $price)
    {
        try {
            $sessionData = [
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($order->currency),
                        'unit_amount' => (int)($order->total_amount * 100),
                        'product_data' => [
                            'name' => $order->items->first()->product->name ?? 'Order #' . $order->order_number,
                            'description' => 'License for ' . ($order->items->first()->product->name ?? 'Software'),
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('orders.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('orders.stripe.cancel'),
                'client_reference_id' => (string) $order->id,
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'user_id' => (string) $order->user_id,
                ],
            ];

            $session = Session::create($sessionData);
            
            // Update the order/payment with the session ID
            $order->payment()->update(['transaction_id' => $session->id]);

            return $session;
        } catch (\Exception $e) {
            Log::error('Failed to create Stripe Checkout Session: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Retrieve a Session from Stripe.
     */
    public function retrieveSession(string $sessionId)
    {
        return Session::retrieve($sessionId);
    }

    /**
     * Get Receipt URL for a transaction.
     */
    public function getReceiptUrl($transactionId)
    {
        try {
            // 1. Try treating it as a Session
            if (str_starts_with($transactionId, 'cs_')) {
                $session = Session::retrieve($transactionId);
                $paymentIntentId = $session->payment_intent;
            } elseif (str_starts_with($transactionId, 'pi_')) {
                $paymentIntentId = $transactionId;
            } else {
                return null;
            }

            if ($paymentIntentId) {
                $pi = \Stripe\PaymentIntent::retrieve($paymentIntentId);
                if ($pi->latest_charge) {
                    $charge = \Stripe\Charge::retrieve($pi->latest_charge);
                    return $charge->receipt_url;
                }
            }
        } catch (\Exception $e) {
            Log::error('Stripe Receipt Retrieval Failed: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Get Webhook Secret from Settings.
     */
    public function getWebhookSecret(): ?string
    {
        return SystemSetting::where('key', 'gateway_stripe_webhook_secret')->value('value');
    }
}
