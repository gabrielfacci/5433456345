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
            // Mascote do Sidebar
            $table->string('sidebar_mascote')->nullable();
            $table->string('sidebar_mascote_titulo')->nullable()->default('Bem-vindo ao');
            $table->string('sidebar_mascote_subtitulo')->nullable()->default('Cassino Online');
            $table->string('sidebar_mascote_titulo_color')->nullable();
            $table->string('sidebar_mascote_subtitulo_color')->nullable();
            $table->string('sidebar_mascote_background')->nullable();

            // Resgatar Código
            $table->string('sidebar_codigo_imagem')->nullable();
            $table->string('sidebar_codigo_titulo')->nullable()->default('RESGATAR CÓDIGO');
            $table->string('sidebar_codigo_subtitulo')->nullable()->default('Resgate seu código promocional');
            $table->string('sidebar_codigo_titulo_color')->nullable();
            $table->string('sidebar_codigo_subtitulo_color')->nullable();
            $table->string('sidebar_codigo_background')->nullable();

            // Missão
            $table->string('sidebar_missao_imagem')->nullable();
            $table->string('sidebar_missao_titulo')->nullable()->default('MISSÃO');
            $table->string('sidebar_missao_subtitulo')->nullable()->default('Complete missões e ganhe');
            $table->string('sidebar_missao_titulo_color')->nullable();
            $table->string('sidebar_missao_subtitulo_color')->nullable();
            $table->string('sidebar_missao_background')->nullable();

            // Promoções
            $table->string('sidebar_promocoes_imagem')->nullable();
            $table->string('sidebar_promocoes_titulo')->nullable()->default('PROMOÇÕES');
            $table->string('sidebar_promocoes_subtitulo')->nullable()->default('Aproveite nossas promoções');
            $table->string('sidebar_promocoes_titulo_color')->nullable();
            $table->string('sidebar_promocoes_subtitulo_color')->nullable();
            $table->string('sidebar_promocoes_background')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_layouts', function (Blueprint $table) {
            // Mascote do Sidebar
            $table->dropColumn('sidebar_mascote');
            $table->dropColumn('sidebar_mascote_titulo');
            $table->dropColumn('sidebar_mascote_subtitulo');
            $table->dropColumn('sidebar_mascote_titulo_color');
            $table->dropColumn('sidebar_mascote_subtitulo_color');
            $table->dropColumn('sidebar_mascote_background');

            // Resgatar Código
            $table->dropColumn('sidebar_codigo_imagem');
            $table->dropColumn('sidebar_codigo_titulo');
            $table->dropColumn('sidebar_codigo_subtitulo');
            $table->dropColumn('sidebar_codigo_titulo_color');
            $table->dropColumn('sidebar_codigo_subtitulo_color');
            $table->dropColumn('sidebar_codigo_background');

            // Missão
            $table->dropColumn('sidebar_missao_imagem');
            $table->dropColumn('sidebar_missao_titulo');
            $table->dropColumn('sidebar_missao_subtitulo');
            $table->dropColumn('sidebar_missao_titulo_color');
            $table->dropColumn('sidebar_missao_subtitulo_color');
            $table->dropColumn('sidebar_missao_background');

            // Promoções
            $table->dropColumn('sidebar_promocoes_imagem');
            $table->dropColumn('sidebar_promocoes_titulo');
            $table->dropColumn('sidebar_promocoes_subtitulo');
            $table->dropColumn('sidebar_promocoes_titulo_color');
            $table->dropColumn('sidebar_promocoes_subtitulo_color');
            $table->dropColumn('sidebar_promocoes_background');
        });
    }
};
