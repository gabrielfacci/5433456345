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
        Schema::create('onda_pay', function (Blueprint $table) {
            $table->id();
            $table->integer("user_id");
            $table->integer("withdrawal_id");
            $table->decimal("amount");
            $table->boolean("status");
            $table->timestamps();
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('ondapay_is_enable')->default(false);
        });
        Schema::table('gateways', function ($table) {
            $table->string('ondapay_uri')->nullable();
            $table->string('ondapay_client')->nullable();
            $table->string('ondapay_secret')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("onda_pay");
    }
};
