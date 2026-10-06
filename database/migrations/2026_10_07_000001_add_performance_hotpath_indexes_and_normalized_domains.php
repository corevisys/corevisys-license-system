<?php

use App\Support\DomainNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Performance remediation: hot-path indexes + stored normalized request domain.
     *
     * Context
     * -------------------------------------------------------------------------
     * The cron commands (license:renew-subscriptions, license:notify-expiring,
     * license:cleanup-expired, license:flag-stale) filter on columns that were
     * never indexed, forcing full table scans as data grows.
     *
     * Additionally, activation history lookups previously loaded every matching
     * row into PHP and normalised domains in-memory (O(n) memory / CPU). We add a
     * persisted `request_domain_normalized` column so those checks become an
     * indexed `exists()` / `count(distinct ...)` at the database level.
     *
     * Production notes
     * -------------------------------------------------------------------------
     * 1. Index creation on large tables may take a metadata lock in MySQL/MariaDB.
     *    Prefer a maintenance window, or `ALGORITHM=INPLACE, LOCK=NONE` on MySQL 8.0+.
     * 2. The backfill is chunked (500 rows) to bound memory. On very large
     *    `license_activations` tables, consider running the backfill out-of-band.
     */
    public function up(): void
    {
        // 1. Add the stored normalized request-domain column.
        if (!Schema::hasColumn('license_activations', 'request_domain_normalized')) {
            Schema::table('license_activations', function (Blueprint $table) {
                $table->string('request_domain_normalized')->nullable()->after('request_domain');
            });
        }

        // 2. Backfill existing rows so indexed lookups are correct for legacy data.
        DB::table('license_activations')
            ->whereNull('request_domain_normalized')
            ->select(['id', 'request_domain'])
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('license_activations')
                        ->where('id', $row->id)
                        ->update([
                            'request_domain_normalized' => DomainNormalizer::normalize($row->request_domain),
                        ]);
                }
            });

        // 3. Composite index covering the (license_id, status) + normalized-domain
        //    lookups used by activation-limit counts and domain-authorisation checks.
        if (!Schema::hasIndex('license_activations', 'license_activations_lic_status_ndomain_idx')) {
            Schema::table('license_activations', function (Blueprint $table) {
                $table->index(
                    ['license_id', 'status', 'request_domain_normalized'],
                    'license_activations_lic_status_ndomain_idx'
                );
            });
        }

        // 4. Licenses hot-path indexes.
        if (!Schema::hasIndex('licenses', 'licenses_status_auto_renew_next_billing_idx')) {
            Schema::table('licenses', function (Blueprint $table) {
                $table->index(
                    ['status', 'auto_renew', 'next_billing_at'],
                    'licenses_status_auto_renew_next_billing_idx'
                );
            });
        }

        if (!Schema::hasIndex('licenses', 'licenses_status_expires_at_idx')) {
            Schema::table('licenses', function (Blueprint $table) {
                $table->index(['status', 'expires_at'], 'licenses_status_expires_at_idx');
            });
        }

        if (!Schema::hasIndex('licenses', 'licenses_status_last_check_at_idx')) {
            Schema::table('licenses', function (Blueprint $table) {
                $table->index(['status', 'last_check_at'], 'licenses_status_last_check_at_idx');
            });
        }

        // 5. Orders / payments status indexes (analytics, webhooks, fulfilment).
        if (!Schema::hasIndex('orders', 'orders_status_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('status', 'orders_status_index');
            });
        }

        if (!Schema::hasIndex('payments', 'payments_status_index')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->index('status', 'payments_status_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('payments', 'payments_status_index')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropIndex('payments_status_index');
            });
        }

        if (Schema::hasIndex('orders', 'orders_status_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_status_index');
            });
        }

        Schema::table('licenses', function (Blueprint $table) {
            if (Schema::hasIndex('licenses', 'licenses_status_last_check_at_idx')) {
                $table->dropIndex('licenses_status_last_check_at_idx');
            }
            if (Schema::hasIndex('licenses', 'licenses_status_expires_at_idx')) {
                $table->dropIndex('licenses_status_expires_at_idx');
            }
            if (Schema::hasIndex('licenses', 'licenses_status_auto_renew_next_billing_idx')) {
                $table->dropIndex('licenses_status_auto_renew_next_billing_idx');
            }
        });

        if (Schema::hasIndex('license_activations', 'license_activations_lic_status_ndomain_idx')) {
            Schema::table('license_activations', function (Blueprint $table) {
                $table->dropIndex('license_activations_lic_status_ndomain_idx');
            });
        }

        if (Schema::hasColumn('license_activations', 'request_domain_normalized')) {
            Schema::table('license_activations', function (Blueprint $table) {
                $table->dropColumn('request_domain_normalized');
            });
        }
    }
};
