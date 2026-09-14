<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('licenses', 'fingerprint_missing_grace')) {
            Schema::table('licenses', function (Blueprint $table) {
                $table->boolean('fingerprint_missing_grace')->default(false)->after('bound_fingerprint');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('licenses', 'fingerprint_missing_grace')) {
            Schema::table('licenses', function (Blueprint $table) {
                $table->dropColumn('fingerprint_missing_grace');
            });
        }
    }
};
