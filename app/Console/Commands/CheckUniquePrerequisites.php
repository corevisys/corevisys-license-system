<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckUniquePrerequisites extends Command
{
    protected $signature = 'db:check-unique-prerequisites';

    protected $description = 'Check database tables for duplicate records before applying unique constraint migrations';

    public function handle(): int
    {
        $this->info('Running pre-flight checks for unique constraints...');

        $hasFailures = false;

        // 1. Check payments.receipt_hash
        $duplicateReceipts = DB::select(
            "SELECT receipt_hash, COUNT(*) as c FROM payments WHERE receipt_hash IS NOT NULL GROUP BY receipt_hash HAVING c > 1"
        );

        if (!empty($duplicateReceipts)) {
            $hasFailures = true;
            $count = count($duplicateReceipts);
            $this->error("FAILED: Found {$count} duplicate groups in payments.receipt_hash.");
            $this->line("Inspect duplicates with: SELECT receipt_hash, count(*) FROM payments WHERE receipt_hash IS NOT NULL GROUP BY receipt_hash HAVING count(*) > 1;");
        } else {
            $this->info('PASS: No duplicates found in payments.receipt_hash.');
        }

        // 2. Check processed_webhooks (gateway, event_id)
        $duplicateEvents = DB::select(
            "SELECT gateway, event_id, COUNT(*) as c FROM processed_webhooks GROUP BY gateway, event_id HAVING c > 1"
        );

        if (!empty($duplicateEvents)) {
            $hasFailures = true;
            $count = count($duplicateEvents);
            $this->error("FAILED: Found {$count} duplicate groups in processed_webhooks (gateway, event_id).");
            $this->line("Inspect duplicates with: SELECT gateway, event_id, count(*) FROM processed_webhooks GROUP BY gateway, event_id HAVING count(*) > 1;");
        } else {
            $this->info('PASS: No duplicates found in processed_webhooks (gateway, event_id).');
        }

        if ($hasFailures) {
            $this->error('Pre-flight check failed! Resolve duplicate data before running migrations.');
            return self::FAILURE;
        }

        $this->info('All pre-flight checks passed. Safe to run unique constraint migrations.');
        return self::SUCCESS;
    }
}
