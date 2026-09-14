<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function verify(Request $request, $id)
    {
        // Action: 'approve' or 'reject'
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string'
        ]);

        $payment = Payment::with('order')->findOrFail($id);

        if ($payment->status !== 'pending') {
            return response()->json(['message' => 'Payment already processed'], 400);
        }

        $payment->verified_by = $request->user()->id; // Admin ID
        $payment->admin_notes = $request->input('notes');

        if ($request->action === 'approve') {
            app(OrderFulfillmentService::class)->fulfillOrder($payment->order);
            $payment->refresh();
            $payment->update([
                'verified_by' => $request->user()->id,
                'admin_notes' => $request->input('notes'),
            ]);

            \App\Services\AuditService::log('payment_approved', $payment, ['status' => 'pending'], ['status' => 'verified']);

            return response()->json(['message' => 'Payment approved and license generated']);
        } else {
            $payment->status = 'failed';
            $payment->save();
            // Order remains pending or move to cancelled?
            $payment->order->update(['status' => 'cancelled']);

            return response()->json(['message' => 'Payment rejected']);
        }
    }
}
