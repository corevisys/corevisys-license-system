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
            $table->boolean('auto_renew')->default(false)->after('expires_at');
            $table->timestamp('next_billing_at')->nullable()->after('auto_renew');
            $table->string('gateway_subscription_id')->nullable()->after('next_billing_at');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn(['auto_renew', 'next_billing_at', 'gateway_subscription_id']);
        });
    }
};
