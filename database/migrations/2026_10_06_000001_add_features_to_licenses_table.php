<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->json('features')->nullable();
        });

        DB::table('licenses')
            ->whereNull('features')
            ->update(['features' => json_encode([])]);
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('features');
        });
    }
};
