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
            $table->string('feedback_titulo')->nullable();
            $table->string('feedback_subtitulo')->nullable();
            $table->string('feedback_titulo_color')->nullable();
            $table->string('feedback_subtitulo_color')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_layouts', function (Blueprint $table) {
            $table->dropColumn([
                'feedback_titulo',
                'feedback_subtitulo',
                'feedback_titulo_color',
                'feedback_subtitulo_color'
            ]);
        });
    }
}; 