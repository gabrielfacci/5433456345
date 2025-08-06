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
        Schema::table('cashback_settings', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('periodicidade');
            $table->decimal('min_cashback', 10, 2)->default(1.00)->after('is_active');
            $table->decimal('max_cashback', 10, 2)->default(1000.00)->after('min_cashback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashback_settings', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'min_cashback', 'max_cashback']);
        });
    }
};
