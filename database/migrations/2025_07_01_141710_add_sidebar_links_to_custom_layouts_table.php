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
            $table->string('sidebar_mascote_link')->nullable();
            $table->string('sidebar_codigo_link')->nullable();
            $table->string('sidebar_missao_link')->nullable();
            $table->string('sidebar_promocoes_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_layouts', function (Blueprint $table) {
            $table->dropColumn('sidebar_mascote_link');
            $table->dropColumn('sidebar_codigo_link');
            $table->dropColumn('sidebar_missao_link');
            $table->dropColumn('sidebar_promocoes_link');
        });
    }
};
