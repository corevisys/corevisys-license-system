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
        Schema::create('license_resets', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('license_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users'); // Assuming admin is a User
            $table->text('reason');
            $table->string('previous_domain')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->integer('reset_count')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('reset_count');
        });

        Schema::dropIfExists('license_resets');
    }
};
