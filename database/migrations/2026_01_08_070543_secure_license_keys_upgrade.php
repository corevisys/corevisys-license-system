<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->string('license_key_hash', 64)->unique()->after('order_id');
        });

        // Migrate existing keys (if any)
        DB::table('licenses')->get()->each(function ($license) {
            DB::table('licenses')
                ->where('id', $license->id)
                ->update(['license_key_hash' => hash('sha256', $license->license_key)]);
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropUnique(['license_key']); // Drop index first
            $table->dropColumn('license_key');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->string('license_key', 500)->nullable();
        });

        // Cannot restore original keys from hash, so we leave them null or handle manually

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('license_key_hash');
        });
    }
};
