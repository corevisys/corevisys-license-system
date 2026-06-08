<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\LicenseService;
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
                $stripeService = new \App\Services\StripePaymentService();
                $session = $stripeService->createCheckoutSession($order, $price);
                $stripeUrl = $session->url;
            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => 'Payment Failed: ' . $e->getMessage()], 500);
            }
        }

        return response()->json([
            'status' => 'success',
            'order' => $order->load('licenses'),
            'stripe_url' => $stripeUrl,
            'message' => 'Order Created'
        ]);
    }

    public function uploadReceipt(Request $request, $id)
    {
        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,png,jpeg|max:2048'
        ]);

        $order = Order::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();

        // Mock Storage: In real app -> $path = $request->file('receipt')->store('receipts');
        $file = $request->file('receipt');
        $path = 'receipts/' . $file->hashName();

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
