<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Services\BKashPaymentService;
use App\Services\BkashRenewalCheckoutService;
use App\Services\OrderFulfillmentService;
use App\Services\StripePaymentService;
use App\Support\OrderStatus;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    /**
     * Create an order (purchase, renewal, or upgrade) and start the selected
     * payment gateway flow (Stripe Checkout, bKash tokenized checkout, or manual).
     */
    public function createOrder(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'price_id' => 'required|exists:product_prices,id',
            'payment_method' => 'required|string|in:online,offline',
            // For offline payments, transaction_id might be manually entered or generated.
            // For online, it comes from the gateway response later, but here we initiate.
            'transaction_id' => 'nullable|string',
            'gateway' => 'nullable|string',
        ]);

        // Ensure the selected price actually belongs to the selected product,
        // otherwise a user could pair a cheap price with an expensive product.
        $price = ProductPrice::where('id', $request->price_id)
            ->where('product_id', $request->product_id)
            ->firstOrFail();
        $product = Product::findOrFail($request->product_id);

        abort_unless($product->is_active, 422, 'Product Unavailable');

        // Create Order
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'user_id' => auth()->id(),
            'total_amount' => $price->amount,
            'currency' => $price->currency,
            'status' => $request->payment_method === 'offline' ? 'pending' : 'awaiting_payment', // Pending verification vs waiting for gateway
            'payment_method' => $request->payment_method,
        ]);

        // Create Order Item
        if (class_exists(OrderItem::class)) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_price_id' => $price->id,
                'price' => $price->amount,
                'license_type' => in_array($price->type, ['trial', 'full', 'subscription']) ? $price->type : 'full',
            ]);
        }

        Log::info('Order Create initiated', $request->all());

        // Create Initial Payment Record
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'gateway' => $request->payment_method === 'offline' ? 'manual' : ($request->gateway ?? 'stripe'),
            'transaction_id' => $request->transaction_id ?? null,
            'amount' => $price->amount,
            'status' => 'pending',
        ]);

        // Handle Stripe Online Payment
        if ($request->payment_method === 'online' && ($request->gateway === 'stripe' || !$request->gateway)) {
            try {
                $stripeService = new StripePaymentService();
                $checkout_session = $stripeService->createCheckoutSession($order, $price);

                Log::info('Stripe Session Created: ' . $checkout_session->id);
                return Inertia::location($checkout_session->url);
            } catch (\Exception $e) {
                Log::error('Stripe Exception: ' . $e->getMessage());
                return back()->with('error', 'Payment Initialization Failed: ' . $e->getMessage());
            }
        }

        // Handle bKash Online Payment (Tokenized Checkout)
        if ($request->payment_method === 'online' && $request->gateway === 'bkash') {
            try {
                $bkashService = new BKashPaymentService();
                $bkashResponse = $bkashService->createPayment($order, $price);

                $payment->update([
                    'transaction_id' => $bkashResponse['paymentID'],
                    'gateway_response' => $bkashResponse,
                ]);

                Log::info('bKash Payment Created: ' . $bkashResponse['paymentID']);
                return Inertia::location($bkashResponse['bkashURL']);
            } catch (\Exception $e) {
                Log::error('bKash Exception: ' . $e->getMessage());
                return back()->with('error', 'Payment Initialization Failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('orders')->with('success', 'Order created successfully!');
    }

    public function stripeSuccess(Request $request)
    {
        try {
            $stripeService = new StripePaymentService();
            $session = $stripeService->retrieveSession($request->get('session_id'));

            if ($session->payment_status === 'paid') {
                $orderId = $session->metadata->order_id ?? $session->client_reference_id;
                $order = Order::with('payment')->findOrFail($orderId);

                // Fulfillment Fallback (if webhook hasn't processed it yet)
                // Use Centralized Service
                try {
                    $fulfillmentService = app(OrderFulfillmentService::class);
                    $result = $fulfillmentService->fulfillOrder($order, [
                        'transaction_id' => $session->payment_intent ?? $session->id,
                        'gateway_response' => $session->toArray()
                    ]);

                    // If result is returned, it means we just fulfilled it. Flash only non-sensitive references.
                    if ($result) {
                        $license = $result['license'];
                        $apiToken = $result['api_token'];

                        if ($license) {
                            session()->flash('new_license_key', 'XXXX-XXXX-' . substr($license->license_key_hash ?? '', -4));
                        }
                        if ($apiToken) {
                            session()->flash('new_api_token', $apiToken);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Success Callback: Fulfillment Error", ['order_id' => $orderId, 'error' => $e->getMessage()]);
                }

                return redirect()->route('dashboard')->with('success', 'Payment successful! Your license and API Key have been generated.');
            }
        } catch (\Exception $e) {
            Log::error("Success Callback Fallback Error: " . $e->getMessage());
            return redirect()->route('orders')->with('error', 'Payment verification failed.');
        }

        return redirect()->route('orders')->with('error', 'Payment was not completed.');
    }

    public function stripeCancel()
    {
        return redirect()->route('orders')->with('info', 'Payment was cancelled.');
    }

    public function bkashCallback(Request $request)
    {
        $paymentID = is_string($request->get('paymentID')) ? trim($request->get('paymentID')) : null;

        if (!$paymentID) {
            return redirect()->route('orders')->with('error', 'Payment was not completed.');
        }

        $payment = Payment::where('gateway', 'bkash')
            ->where(function ($query) use ($paymentID) {
                $query->where('transaction_id', $paymentID)
                    ->orWhere('gateway_response->paymentID', $paymentID);
            })
            ->with(['order.licenses', 'order.items.product', 'order.license'])
            ->first();

        if (!$payment || !$payment->order) {
            Log::error('bKash Callback: Payment not found.', ['payment_id' => $paymentID]);
            return redirect()->route('orders')->with('error', 'Payment verification failed.');
        }

        if (auth()->check() && (int) $payment->user_id !== (int) auth()->id()) {
            Log::warning('bKash Callback: User mismatch.', [
                'payment_id' => $paymentID,
                'payment_user_id' => $payment->user_id,
                'auth_user_id' => auth()->id(),
            ]);
            return redirect()->route('orders')->with('error', 'Unauthorized payment callback.');
        }

        $lock = Cache::lock("bkash_payment_process:{$paymentID}", 15);
        try {
            $lock->block(5);
        } catch (LockTimeoutException $e) {
            Log::warning('bKash Callback: Lock timeout.', ['payment_id' => $paymentID]);
            return redirect()->route('orders')->with('info', 'Your payment is currently being processed. Please refresh in a moment.');
        }

        try {
            $payment->refresh();
            $order = $payment->order;

            if ($order && $order->type === 'renewal' && $order->renewal_cycle_at
                && !app(BkashRenewalCheckoutService::class)->isMatchingRenewalPayment($payment)) {
                Log::warning('bKash Callback: Renewal payment/order/license mismatch.', [
                    'payment_id' => $paymentID,
                    'payment_record_id' => $payment->id,
                    'order_id' => $order->id,
                ]);
                return redirect()->route('orders')->with('error', 'Payment verification failed.');
            }

            if ($order && ($order->status === OrderStatus::COMPLETED || $payment->status === 'verified')) {
                Log::info('bKash Callback: Payment/order already completed. Idempotent return.', ['payment_id' => $paymentID]);
                $existingLicense = $order->licenses()->first()
                    ?? ($order->license_id ? \App\Models\License::find($order->license_id) : null)
                    ?? $order->license;

                if ($existingLicense) {
                    session()->flash('new_license_key', 'XXXX-XXXX-' . substr($existingLicense->license_key_hash ?? '', -4));
                }
                return redirect()->route('dashboard')->with('success', 'Payment successful! Your license is active.');
            }

            $bkashService = app(BKashPaymentService::class);
            $result = $bkashService->executeOrQueryPayment($paymentID);

            if (($result['transactionStatus'] ?? '') === 'Completed') {
                if ($order->type === 'renewal' && $order->renewal_cycle_at && !$bkashService->isPaymentAmountValid($order, $result)) {
                    Log::warning('bKash Callback: Renewal amount mismatch.', [
                        'payment_id' => $paymentID,
                        'order_id' => $order->id,
                    ]);
                    return redirect()->route('orders')->with('error', 'Payment verification failed.');
                }

                try {
                    $fulfillmentService = app(OrderFulfillmentService::class);
                    $trxID = $result['trxID'] ?? $result['paymentID'];
                    $fulfillment = $fulfillmentService->fulfillOrder($order, [
                        'transaction_id' => $trxID,
                        'gateway_response' => array_merge($payment->gateway_response ?? [], $result, ['paymentID' => $paymentID]),
                    ]);

                    if ($fulfillment) {
                        $license = $fulfillment['license'];
                        $apiToken = $fulfillment['api_token'];

                        if ($license) {
                            session()->flash('new_license_key', 'XXXX-XXXX-' . substr($license->license_key_hash ?? '', -4));
                        }
                        if ($apiToken) {
                            session()->flash('new_api_token', $apiToken);
                        }
                    }

                    return redirect()->route('dashboard')->with('success', 'Payment successful! Your license and API Key have been generated.');
                } catch (\Exception $e) {
                    Log::error('bKash Callback: Fulfillment error.', ['payment_id' => $paymentID, 'error' => $e->getMessage()]);
                    return redirect()->route('orders')->with('error', 'Payment verification failed.');
                }
            }

            Log::warning('bKash Callback: Payment not completed.', [
                'payment_id' => $paymentID,
                'status' => $result['transactionStatus'] ?? 'unknown',
            ]);
            return redirect()->route('orders')->with('error', 'Payment was not completed.');
        } finally {
            optional($lock)->release();
        }
    }
}