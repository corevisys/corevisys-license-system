<?php

namespace App\Console\Commands;

use App\Services\ReceiptStorageService;
use Illuminate\Console\Command;

class PruneReceipts extends Command
{
    protected $signature = 'receipts:prune {--days=}';

    protected $description = 'Prune expired uploaded receipts and associated payment records';

    public function handle(ReceiptStorageService $storage): int
    {
        $days = $this->option('days');
        $deleted = $storage->pruneExpiredReceipts($days !== null ? (int) $days : null);

        $this->info("Deleted {$deleted} expired receipt records.");

        return self::SUCCESS;
    }
}
