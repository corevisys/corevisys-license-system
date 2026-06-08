<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\LicenseService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

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
            $payment->status = 'verified';
            $payment->save();

            \App\Services\AuditService::log(
                'payment_approved',
                $payment,
                ['status' => 'pending'],
                ['status' => 'verified', 'notes' => $payment->admin_notes]
            );

            // Mark Order Completed
            $payment->order->update(['status' => 'completed']);

            // Generate License (if not exists)
            // Simplified logic: Assume 1 product per order
            $orderItem = $payment->order->items()->first();
            if ($orderItem) {
                $this->licenseService->createLicense(
                    $payment->order,
                    $orderItem->product,
                    $orderItem->license_type
                );
            }

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
