<?php

namespace App\Services;

use App\Models\Order;
use App\Support\OrderStatus;
use Illuminate\Support\Facades\DB;
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
        if ($order->status === OrderStatus::COMPLETED) {
            Log::info("OrderFulfillment: Order {$order->order_number} already completed. Skipping.");
            return;
        }

        try {
            return DB::transaction(function () use ($order, $paymentData) {
                $item = $order->items()->first();
                if (!$item) {
                    throw new \RuntimeException("Order {$order->order_number} has no items to fulfill.");
                }

                $license = match ($order->type) {
                    'renewal' => $this->licenseService->renewLicense($order, $item->product),
                    'upgrade' => $this->licenseService->upgradeLicense($order, $item->product),
                    default => $this->licenseService->createLicense($order, $item->product, $item->license_type ?? 'full'),
                };

                $apiToken = $this->licenseService->getOrCreateApiToken($order->user);

                $order->update(['status' => OrderStatus::COMPLETED]);

                if ($order->payment) {
                    $paymentUpdates = ['status' => 'verified'];
                    if (!empty($paymentData['transaction_id'])) {
                        $paymentUpdates['transaction_id'] = $paymentData['transaction_id'];
                    }
                    if (!empty($paymentData['gateway_response'])) {
                        $paymentUpdates['gateway_response'] = $paymentData['gateway_response'];
                    }
                    $order->payment->update($paymentUpdates);
                }

                Log::info("OrderFulfillment: Fulfillment complete for Order {$order->order_number}", [
                    'license_id' => $license->id,
                    'api_token_generated' => (bool) $apiToken,
                ]);

                return ['license' => $license, 'api_token' => $apiToken];
            });
        } catch (\Exception $e) {
            Log::error("OrderFulfillment: License generation failed for Order {$order->order_number}", [
                'error' => $e->getMessage()
            ]);
            throw $e; // Re-throw to ensure caller knows it failed (e.g., specific error handling)
        }
        
        return null;
    }
}
