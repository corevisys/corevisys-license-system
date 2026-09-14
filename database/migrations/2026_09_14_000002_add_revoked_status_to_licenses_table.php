<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('licenses') || !Schema::hasColumn('licenses', 'status')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $current = DB::selectOne("SHOW COLUMNS FROM licenses WHERE Field = 'status'");
            $type = $current?->Type ?? '';

            if (stripos($type, 'enum') === false || stripos($type, 'revoked') !== false) {
                return;
            }

            DB::statement("ALTER TABLE licenses MODIFY COLUMN status ENUM('active', 'inactive', 'expired', 'suspended', 'revoked') NOT NULL DEFAULT 'active'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE licenses MODIFY COLUMN status ENUM('active', 'inactive', 'expired', 'suspended') NOT NULL DEFAULT 'active'");
        }
    }
};
