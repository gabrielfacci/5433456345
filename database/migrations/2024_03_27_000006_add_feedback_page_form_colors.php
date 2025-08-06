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
            $table->string('feedback_page_background', 20)->default('#12131d');
            $table->string('feedback_page_text_color', 20)->default('#ffffff');
            $table->string('feedback_page_title_color', 20)->default('#ffffff');
            $table->string('feedback_page_button_color', 20)->default('#0073D2');
            $table->string('feedback_page_button_text_color', 20)->default('#ffffff');
            $table->string('feedback_page_fade_color', 20)->default('#12131d');
            $table->string('feedback_page_page_background', 20)->default('#12131d');
            $table->string('feedback_page_form_background', 20)->default('#212425');
            $table->string('feedback_share_title_color', 20)->default('#ffffff');
            $table->string('feedback_share_background_color', 20)->default('#1a1b1f');
            $table->string('feedback_page_form_text_color', 20)->default('#ffffff');
            $table->string('feedback_page_form_button_color', 20)->default('#0073D2');
            $table->string('feedback_page_form_button_text_color', 20)->default('#ffffff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_layouts', function (Blueprint $table) {
            $table->dropColumn([
                'feedback_page_background',
                'feedback_page_text_color',
                'feedback_page_title_color',
                'feedback_page_button_color',
                'feedback_page_button_text_color',
                'feedback_page_fade_color',
                'feedback_page_page_background',
                'feedback_page_form_background',
                'feedback_share_title_color',
                'feedback_share_background_color',
                'feedback_page_form_text_color',
                'feedback_page_form_button_color',
                'feedback_page_form_button_text_color'
            ]);
        });
    }
}; 