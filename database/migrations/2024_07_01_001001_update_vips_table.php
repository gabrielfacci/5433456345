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
            if (!Schema::hasColumn('vips', 'title')) {
                $table->string('title')->nullable();
            }
            if (!Schema::hasColumn('vips', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('vips', 'image')) {
                $table->string('image')->nullable();
            }
            if (!Schema::hasColumn('vips', 'required_achievements')) {
                $table->integer('required_achievements')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vips', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'image', 'required_achievements']);
        });
    }
}; 