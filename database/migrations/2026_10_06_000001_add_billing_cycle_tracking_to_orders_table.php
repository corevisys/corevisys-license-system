<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('renewal_cycle_at')->nullable()->after('type');
            $table->timestamp('renewal_link_email_sent_at')->nullable()->after('renewal_cycle_at');
            $table->unique(
                ['license_id', 'type', 'renewal_cycle_at'],
                'orders_license_type_renewal_cycle_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_license_type_renewal_cycle_unique');
            $table->dropColumn(['renewal_cycle_at', 'renewal_link_email_sent_at']);
        });
    }
};
