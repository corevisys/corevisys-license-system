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
        Schema::table('license_resets', function (Blueprint $table) {
            $table->string('previous_fingerprint')->nullable()->after('previous_domain');
        });
    }

    public function down(): void
    {
        Schema::table('license_resets', function (Blueprint $table) {
            $table->dropColumn('previous_fingerprint');
        });
    }
};
