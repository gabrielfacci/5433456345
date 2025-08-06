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
            $table->string('feedback_page_background', 20)->default('#24262B');
            $table->string('feedback_page_text_color', 20)->default('#ffffff');
            $table->string('feedback_page_title_color', 20)->default('#ffffff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_layouts', function (Blueprint $table) {
            $table->dropColumn('feedback_page_background');
            $table->dropColumn('feedback_page_text_color');
            $table->dropColumn('feedback_page_title_color');
        });
    }
}; 