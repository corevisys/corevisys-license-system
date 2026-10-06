<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Pre-flight Check:
     * -------------------------------------------------------------------------
     * Aborts before altering schema if any duplicate values exist on
     * `payments.receipt_hash`.
     *
     * Note on `processed_webhooks`:
     * -------------------------------------------------------------------------
     * `processed_webhooks` already enforces compound unique (gateway, event_id)
     * created in 2026_01_08_072325_create_processed_webhooks_table.php.
     * We drop any standalone unique(event_id) that may have been created
     * previously, as event IDs are only guaranteed unique per gateway.
     */
    public function up(): void
    {
        // 1. Pre-flight check: Duplicate non-NULL receipt_hash in payments
        $duplicateReceipts = DB::select(
            "SELECT receipt_hash, COUNT(*) as c FROM payments WHERE receipt_hash IS NOT NULL GROUP BY receipt_hash HAVING c > 1"
        );

        if (!empty($duplicateReceipts)) {
            $count = count($duplicateReceipts);
            throw new \RuntimeException(
                "Cannot apply unique constraint: Found {$count} duplicate groups in payments.receipt_hash. " .
                "Inspect duplicates using: SELECT receipt_hash, count(*) FROM payments WHERE receipt_hash IS NOT NULL GROUP BY receipt_hash HAVING count(*) > 1;"
            );
        }

        // 2. Remove standalone unique(event_id) if present, preserving compound (gateway, event_id)
        Schema::table('processed_webhooks', function (Blueprint $table) {
            if (Schema::hasIndex('processed_webhooks', 'processed_webhooks_event_id_unique')) {
                $table->dropUnique('processed_webhooks_event_id_unique');
            }
        });

        // 3. Apply unique constraint on payments.receipt_hash
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasIndex('payments', 'payments_receipt_hash_unique')) {
                $table->unique('receipt_hash', 'payments_receipt_hash_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasIndex('payments', 'payments_receipt_hash_unique')) {
                $table->dropUnique('payments_receipt_hash_unique');
            }
        });
    }
};
