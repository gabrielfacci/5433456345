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
            $table->string('feedback_page_button_color', 20)->default('#0073D2');
            $table->string('feedback_page_button_text_color', 20)->default('#ffffff');
            $table->string('feedback_page_fade_color', 20)->default('#00000080');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_layouts', function (Blueprint $table) {
            $table->dropColumn('feedback_page_button_color');
            $table->dropColumn('feedback_page_button_text_color');
            $table->dropColumn('feedback_page_fade_color');
        });
    }
}; 