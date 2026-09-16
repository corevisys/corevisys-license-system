<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('system_settings')
            ->where('key', 'default_theme')
            ->where('value', 'dark-modern')
            ->update(['value' => 'terminal', 'updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')
            ->where('key', 'default_theme')
            ->where('value', 'terminal')
            ->update(['value' => 'dark-modern', 'updated_at' => now()]);
    }
};