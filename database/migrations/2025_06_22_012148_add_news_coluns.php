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
        Schema::table('settings', function (Blueprint $table) {
            $table->string("game_code_rounds_free")->nullable();
            $table->string("rounds_free")->nullable();
            $table->decimal("amount_rounds_free")->default(0);
            $table->boolean("game_free_rounds_active")->default(false);
        });
        Schema::create('logs_rounds_free', function (Blueprint $table){
            $table->id();
            $table->string("game_code");
            $table->string("username");
            $table->boolean("status")->default(false);
            $table->string("message");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['game_code_rounds_free', 'rounds_free', 'amount_rounds_free', 'game_free_rounds_active']);
        });
        Schema::dropIfExists('logs_rounds_free');
    }
};
