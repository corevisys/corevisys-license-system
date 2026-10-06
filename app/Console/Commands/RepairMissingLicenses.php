<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderFulfillmentService;
use App\Support\OrderStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RepairMissingLicenses extends Command
{
    protected $signature = 'license:repair-missing {--dry-run : List eligible orders without changing data} {--order= : Limit the run to one order ID} {--force : Fulfill eligible orders}';

    protected $description = 'Find completed orders that are missing their license';

    public function handle(OrderFulfillmentService $fulfillmentService): int
    {
        $dryRun = $this->option('dry-run') || !$this->option('force');
        $orders = Order::query()
            ->where('status', OrderStatus::COMPLETED)
            ->whereDoesntHave('licenses')
            ->with(['payment', 'items.product'])
            ->when($this->option('order'), fn ($query, $orderId) => $query->whereKey($orderId))
            ->orderBy('id')
            ->get();

        if ($dryRun) {
            $this->info('Dry run only. Pass --force to fulfill eligible orders.');
        }

        $candidateCount = 0;
        $failed = false;

        foreach ($orders as $order) {
            $item = $order->items->first();
            $reason = match (true) {
                !$item => 'no item',
                !$item->product => 'no product',
                $order->payment?->status !== 'verified' => 'payment not verified',
                default => null,
            };

            if ($reason) {
                $this->line("Skipped order={$order->id} reason={$reason}");
                continue;
            }

            $this->line(sprintf(
                'Candidate order=%s user=%s product=%s gateway=%s',
                $order->id,
                $order->user_id,
                $item->product->name,
                $order->payment->gateway
            ));
            $candidateCount++;

            if ($dryRun) {
                continue;
            }

            if ($order->licenses()->exists()) {
                $this->line("Skipped order={$order->id} reason=license already exists");
                continue;
            }

            try {
                $result = $fulfillmentService->fulfillOrder($order);
                Log::info('Missing license repair fulfilled order', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'product_id' => $item->product->id,
                    'license_id' => $result['license']->id,
                ]);
                $this->info("Fulfilled order={$order->id}");
            } catch (Throwable $e) {
                Log::error('Missing license repair failed', [
                    'order_id' => $order->id,
                    'exception' => get_class($e),
                ]);
                $this->error("Failed order={$order->id}");
                $failed = true;
            }
        }

        $this->info("{$candidateCount} eligible order(s) found.");

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}