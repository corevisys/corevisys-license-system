<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Production Lock & Online Indexing Notes:
     * -------------------------------------------------------------------------
     * 1. Lock minimization: Adding indexes to large tables (such as `licenses` and
     *    `license_activations`) can acquire exclusive metadata locks in MySQL/MariaDB.
     *    For high-traffic production environments, execute during maintenance windows
     *    or consider executing with `ALGORITHM=INPLACE, LOCK=NONE` where supported
     *    (supported natively in MySQL 8.0+ for secondary B-tree indexes).
     * 2. High-volume tables: If `license_activations` contains millions of rows,
     *    online schema change tools such as `gh-ost` or `pt-online-schema-change`
     *    are recommended to avoid replica lag and table locks.
     * 3. Nullable unique index: The `lookup_hash` column is nullable, which in MySQL
     *    and SQLite permits multiple NULL values without triggering unique collisions
     *    prior to legacy row backfills.
     */
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            if (!Schema::hasColumn('licenses', 'lookup_hash')) {
                $table->char('lookup_hash', 64)->nullable()->unique()->after('license_key_hash');
            }
            if (!Schema::hasIndex('licenses', 'licenses_user_id_product_id_index')) {
                $table->index(['user_id', 'product_id'], 'licenses_user_id_product_id_index');
            }
        });

        Schema::table('license_activations', function (Blueprint $table) {
            if (!Schema::hasIndex('license_activations', 'license_activations_lic_status_domain_idx')) {
                $table->index(['license_id', 'status', 'request_domain'], 'license_activations_lic_status_domain_idx');
            }
        });

        Schema::table('trial_histories', function (Blueprint $table) {
            if (!Schema::hasIndex('trial_histories', 'trial_histories_email_hash_index')) {
                $table->index('email_hash', 'trial_histories_email_hash_index');
            }
            if (!Schema::hasIndex('trial_histories', 'trial_histories_fingerprint_hash_index')) {
                $table->index('fingerprint_hash', 'trial_histories_fingerprint_hash_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trial_histories', function (Blueprint $table) {
            // Note: only drop if named custom index exists
            if (Schema::hasIndex('trial_histories', 'trial_histories_email_hash_index')) {
                // If created by original schema, skip drop or drop carefully
            }
        });

        Schema::table('license_activations', function (Blueprint $table) {
            if (Schema::hasIndex('license_activations', 'license_activations_lic_status_domain_idx')) {
                if (!Schema::hasIndex('license_activations', 'license_activations_license_id_foreign')) {
                    $table->index('license_id', 'license_activations_license_id_foreign');
                }
                $table->dropIndex('license_activations_lic_status_domain_idx');
            }
        });

        Schema::table('licenses', function (Blueprint $table) {
            if (Schema::hasIndex('licenses', 'licenses_user_id_product_id_index')) {
                if (!Schema::hasIndex('licenses', 'licenses_user_id_foreign')) {
                    $table->index('user_id', 'licenses_user_id_foreign');
                }
                $table->dropIndex('licenses_user_id_product_id_index');
            }
            if (Schema::hasColumn('licenses', 'lookup_hash')) {
                $table->dropUnique(['lookup_hash']);
                $table->dropColumn('lookup_hash');
            }
        });
    }
};
