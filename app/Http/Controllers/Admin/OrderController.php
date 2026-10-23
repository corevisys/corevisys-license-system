<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'payment'])->latest()->get()->map(fn ($o) => [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'user_name' => $o->user->name,
            'user_email' => $o->user->email,
            'total_amount' => $o->total_amount,
            'currency' => $o->currency,
            'status' => $o->status,
            'payment_method' => $o->payment_method,
            'created_at' => $o->created_at->format('Y-m-d H:i'),
            'transaction_id' => $o->payment?->transaction_id ?? 'N/A', // Assuming one payment per order for now
        ]);

        return Inertia::render('Admin/Orders', ['orders' => $orders]);
    }

    /**
     * Admin manual verification of an offline/receipt payment. Triggers the
     * centralized fulfillment service and surfaces a reviewable error on failure.
     */
    public function verify($id, Request $request)
    {
        $order = Order::with('payment')->findOrFail($id);

        try {
            if (!$order->payment) {
                throw new \RuntimeException('Order has no payment record.');
            }

            app(OrderFulfillmentService::class)->fulfillOrder($order, [
                'verified_by' => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin order fulfillment failed', [
                'order_id' => $order->id,
                'exception' => get_class($e),
            ]);

            $message = 'Could not confirm this order. Please review it and try again.';
            return back()->with('error', $message)->withErrors(['fulfillment' => $message]);
        }

        return back()->with('success', 'Order verified successfully.');
    }
}