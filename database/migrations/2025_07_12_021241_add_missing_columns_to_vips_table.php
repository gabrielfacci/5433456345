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
        Schema::table('vips', function (Blueprint $table) {
            // Adiciona colunas que estão faltando baseado na migration original
            if (!Schema::hasColumn('vips', 'bet_symbol')) {
                $table->string('bet_symbol')->nullable();
            }
            if (!Schema::hasColumn('vips', 'bet_required')) {
                $table->bigInteger('bet_required')->nullable();
            }
            if (!Schema::hasColumn('vips', 'bet_bonus')) {
                $table->bigInteger('bet_bonus')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vips', function (Blueprint $table) {
            $table->dropColumn(['bet_symbol', 'bet_required', 'bet_bonus']);
        });
    }
};
