<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_layouts', function (Blueprint $table) {
            $table->string('feedback_page_background')->nullable();
            $table->string('feedback_page_text_color')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('custom_layouts', function (Blueprint $table) {
            $table->dropColumn([
                'feedback_page_background',
                'feedback_page_text_color'
            ]);
        });
    }
}; 