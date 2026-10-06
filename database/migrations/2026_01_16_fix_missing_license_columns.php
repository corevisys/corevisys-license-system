<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            if (!Schema::hasColumn('licenses', 'secret_salt')) {
                $table->string('secret_salt')->nullable()->after('status');
            }
            if (!Schema::hasColumn('licenses', 'activation_limit')) {
                $table->integer('activation_limit')->default(1)->after('secret_salt');
            }
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            //
        });
    }
};
