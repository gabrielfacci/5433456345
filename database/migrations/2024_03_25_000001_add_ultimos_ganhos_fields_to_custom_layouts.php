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
        Schema::table('custom_layouts', function (Blueprint $table) {
            $table->string('mascote_ganhos')->nullable();
            $table->string('ultimos_ganhos_player_color', 20)->nullable();
            $table->string('ultimos_ganhos_valor_color', 20)->nullable();
            $table->string('ultimos_ganhos_titulo_color', 20)->nullable();
            $table->string('ultimos_ganhos_background_color', 20)->nullable();
            $table->string('ultimos_ganhos_titulo_texto')->nullable();
            $table->string('banner_deposito1')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_layouts', function (Blueprint $table) {
            $table->dropColumn([
                'mascote_ganhos',
                'ultimos_ganhos_player_color',
                'ultimos_ganhos_valor_color',
                'ultimos_ganhos_titulo_color',
                'ultimos_ganhos_background_color',
                'ultimos_ganhos_titulo_texto',
                'banner_deposito1'
            ]);
        });
    }
}; 