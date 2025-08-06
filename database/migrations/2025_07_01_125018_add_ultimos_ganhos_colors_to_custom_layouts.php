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
            $table->string('ultimos_ganhos_subtitulo_color')->nullable();
            $table->string('ultimos_ganhos_fade_color')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_layouts', function (Blueprint $table) {
            $table->dropColumn('ultimos_ganhos_subtitulo_color');
            $table->dropColumn('ultimos_ganhos_fade_color');
        });
    }
};
