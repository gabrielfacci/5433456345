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
        Schema::table('raspadinhas', function (Blueprint $table) {
            $table->integer('win_chance_percentage')->default(5)->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raspadinhas', function (Blueprint $table) {
            $table->dropColumn('win_chance_percentage');
        });
    }
};
