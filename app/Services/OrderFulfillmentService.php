<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderFulfillmentService
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Fulfill an order: Mark as completed, generate license, API token, etc.
     * Idempotent: Can be called multiple times safely.
     */
    public function fulfillOrder(Order $order, array $paymentData = [])
    {
        if ($order->status === 'completed') {
            Log::info("OrderFulfillment: Order {$order->order_number} already completed. Skipping.");
            return;
        }

        // 1. Update Order Status
        $order->update(['status' => 'completed']);

        // 2. Update Payment Logic
        if ($order->payment) {
            $paymentUpdates = [
                'status' => 'verified',
            ];
            if (!empty($paymentData['transaction_id'])) {
                $paymentUpdates['transaction_id'] = $paymentData['transaction_id'];
            }
            if (!empty($paymentData['gateway_response'])) {
                $paymentUpdates['gateway_response'] = $paymentData['gateway_response'];
            }
            $order->payment->update($paymentUpdates);
        }

        // 3. Generate License & API Token
        try {
            $item = $order->items()->first();
            if ($item) {
                // Ensure duplicate check handles this in LicenseService, but createLicense is robust
                $license = null;
                switch ($order->type) {
                    case 'renewal':
                        $license = $this->licenseService->renewLicense($order, $item->product);
                        break;
                    case 'upgrade':
                        $license = $this->licenseService->upgradeLicense($order, $item->product);
                        break;
                    case 'purchase':
                    default:
                        $license = $this->licenseService->createLicense($order, $item->product, $item->license_type ?? 'full');
                        break;
                }
                
                // Ensure API Token (Sanctum)
                $apiToken = $this->licenseService->getOrCreateApiToken($order->user);

                Log::info("OrderFulfillment: Fulfillment complete for Order {$order->order_number}", [
                    'license_id' => $license->id,
                    'api_token_generated' => (bool)$apiToken
                ]);

                return [
                    'license' => $license,
                    'api_token' => $apiToken
                ];
            }
        } catch (\Exception $e) {
            Log::error("OrderFulfillment: License generation failed for Order {$order->order_number}", [
                'error' => $e->getMessage()
            ]);
            throw $e; // Re-throw to ensure caller knows it failed (e.g., specific error handling)
        }
        
        return null;
    }
}
