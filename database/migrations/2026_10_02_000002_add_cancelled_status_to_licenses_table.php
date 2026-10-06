<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds 'cancelled' to the licenses.status enum so that server-revoked
     * / operator-cancelled licenses can be stored without MySQL truncation.
     * SQLite stores enums as varchar so this migration is a no-op there.
     */
    public function up(): void
    {
        if (! Schema::hasTable('licenses') || ! Schema::hasColumn('licenses', 'status')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $current = DB::selectOne("SHOW COLUMNS FROM licenses WHERE Field = 'status'");
        $type = $current?->Type ?? '';

        // Only alter if 'cancelled' is not already present
        if (stripos($type, 'cancelled') !== false) {
            return;
        }

        DB::statement("ALTER TABLE licenses MODIFY COLUMN status ENUM('active', 'inactive', 'expired', 'suspended', 'revoked', 'cancelled') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        // Convert any 'cancelled' rows to 'inactive' before removing the value
        DB::table('licenses')->where('status', 'cancelled')->update(['status' => 'inactive']);

        DB::statement("ALTER TABLE licenses MODIFY COLUMN status ENUM('active', 'inactive', 'expired', 'suspended', 'revoked') NOT NULL DEFAULT 'active'");
    }
};
