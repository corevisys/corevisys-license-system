<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\BKashPaymentService;
use App\Services\LicenseService;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    protected $licenseService;
    protected $currencyService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
        $this->currencyService = new \App\Services\CurrencyService();
    }

    public function store(Request $request)
    {
        // Simple order creation (Single Product for MVP)
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'gateway' => 'required|string', // stripe, manual, etc
        ]);

        $user = $request->user();
        $product = Product::findOrFail($request->product_id);

        // Find price (default to first full price found for MVP or passed in request)
        // For robustness, request should send price_id, but we'll simplify.
        $price = $product->prices()->where('type', 'full')->first();
        if (!$price) {
            return response()->json(['status' => false, 'message' => 'Product Unavailable'], 400);
        }

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'total_amount' => $price->amount,
            'currency' => $price->currency,
            'status' => 'pending',
            'payment_method' => $request->gateway,
        ]);

        // Add Item
        $order->items()->create([
            'product_id' => $product->id,
            'price' => $price->amount,
            'license_type' => 'full',
        ]);

        // Handle Stripe
        $stripeUrl = null;
        if ($request->gateway === 'stripe') {
            try {
                // Ensure a payment record exists so the checkout session can be linked
                // and the webhook/success-callback can fulfill the order.
                $order->payments()->create([
                    'user_id' => $user->id,
                    'gateway' => 'stripe',
                    'amount' => $price->amount,
                    'status' => 'pending',
                ]);

                $stripeService = new \App\Services\StripePaymentService();
                $session = $stripeService->createCheckoutSession($order, $price);
                $stripeUrl = $session->url;
            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => 'Payment Failed: ' . $e->getMessage()], 500);
            }
        }

        // Handle bKash (Tokenized Checkout)
        $bkashUrl = null;
        if ($request->gateway === 'bkash') {
            try {
                $order->payments()->create([
                    'user_id' => $user->id,
                    'gateway' => 'bkash',
                    'amount' => $price->amount,
                    'status' => 'pending',
                ]);

                $bkashService = new BKashPaymentService();
                $bkashResponse = $bkashService->createPayment($order, $price);

                $order->payment()->update([
                    'transaction_id' => $bkashResponse['paymentID'],
                    'gateway_response' => $bkashResponse,
                ]);

                $bkashUrl = $bkashResponse['bkashURL'];
            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => 'Payment Failed: ' . $e->getMessage()], 500);
            }
        }

        return response()->json([
            'status' => 'success',
            'order' => $order->load('licenses'),
            'stripe_url' => $stripeUrl,
            'bkash_url' => $bkashUrl,
            'message' => 'Order Created'
        ]);
    }

    /**
     * Confirm a bKash payment from a mobile client. The user completes payment
     * in the bKash app/WebView and this endpoint executes + fulfills the order.
     */
    public function executeBkash(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|string',
        ]);

        $payment = Payment::where('gateway', 'bkash')
            ->where('transaction_id', $request->payment_id)
            ->where('user_id', $request->user()->id)
            ->with('order')
            ->firstOrFail();

        $bkashService = new BKashPaymentService();
        $result = $bkashService->executePayment($request->payment_id);

        if (($result['transactionStatus'] ?? '') === 'Completed') {
            $fulfillment = app(OrderFulfillmentService::class)->fulfillOrder($payment->order, [
                'transaction_id' => $result['trxID'] ?? $result['paymentID'],
                'gateway_response' => $result,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment verified. License generated.',
                'license_key' => $fulfillment['license']->raw_key ?? $fulfillment['license']->license_key,
                'transaction_id' => $result['trxID'] ?? $result['paymentID'],
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Payment not completed.',
            'transaction_status' => $result['transactionStatus'] ?? null,
        ], 402);
    }

    public function uploadReceipt(Request $request, $id)
    {
        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,png,jpeg|max:2048'
        ]);

        $order = Order::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();

        $file = $request->file('receipt');

        // Store the receipt on the local disk.
        $path = $file->store('receipts', 'local');

        // Security: Duplicate Receipt Hashing Prevention
        $receiptHash = hash_file('sha256', $file->getPathname());
        $exists = \App\Models\Payment::where('receipt_hash', $receiptHash)->exists();
        if ($exists) {
            return response()->json(['status' => false, 'message' => 'This receipt has already been submitted.'], 400);
        }

        // Create Payment Entry
        $exchangeRate = $this->currencyService->getRate($order->currency);
        $baseAmount = $this->currencyService->convertToBase($order->total_amount, $order->currency);

        $order->payments()->create([
            'user_id' => $request->user()->id,
            'gateway' => 'offline',
            'amount' => $order->total_amount,
            'exchange_rate' => $exchangeRate,
            'base_currency_amount' => $baseAmount,
            'status' => 'pending',
            'payment_proof_path' => $path,
            'receipt_hash' => $receiptHash
        ]);

        $order->update(['status' => 'awaiting_payment']);

        return response()->json([
            'status' => 'success',
            'message' => 'Receipt uploaded. Waiting for admin approval.'
        ]);
    }
}
