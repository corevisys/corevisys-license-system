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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->char('currency', 3)->unique(); // e.g., 'BDT'
            $table->decimal('rate_to_base', 12, 4); // e.g., 120.00
            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('exchange_rate', 12, 4)->default(1.0000)->after('amount');
            $table->decimal('base_currency_amount', 12, 2)->nullable()->after('exchange_rate');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['exchange_rate', 'base_currency_amount']);
        });
        Schema::dropIfExists('exchange_rates');
    }
};
