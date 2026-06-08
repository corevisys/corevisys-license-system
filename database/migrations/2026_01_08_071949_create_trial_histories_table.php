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
        Schema::create('trial_histories', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint_hash')->nullable()->index();
            $table->string('email_hash')->nullable()->index();
            $table->string('ip_hash')->nullable()->index();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trial_histories');
    }
};
