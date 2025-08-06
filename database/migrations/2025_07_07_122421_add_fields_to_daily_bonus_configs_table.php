<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_bonus_configs', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('cycle_hours');
            $table->enum('bonus_type', ['balance_bonus', 'balance_withdrawal'])
                  ->default('balance_bonus')
                  ->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_bonus_configs', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'bonus_type']);
        });
    }
};
