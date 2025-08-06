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
            // Cores da página de feedback
            if (!Schema::hasColumn('custom_layouts', 'feedback_page_page_background')) {
                $table->string('feedback_page_page_background', 20)->default('#1a1b1f');
            }
            if (!Schema::hasColumn('custom_layouts', 'feedback_page_background')) {
                $table->string('feedback_page_background', 20)->default('#1a1b1f');
            }
            if (!Schema::hasColumn('custom_layouts', 'feedback_page_form_background')) {
                $table->string('feedback_page_form_background', 20)->default('#24262B');
            }
            if (!Schema::hasColumn('custom_layouts', 'feedback_page_title_color')) {
                $table->string('feedback_page_title_color', 20)->default('#ffffff');
            }
            if (!Schema::hasColumn('custom_layouts', 'feedback_page_text_color')) {
                $table->string('feedback_page_text_color', 20)->default('#ffffff');
            }
            if (!Schema::hasColumn('custom_layouts', 'feedback_share_title_color')) {
                $table->string('feedback_share_title_color', 20)->default('#ffffff');
            }
            if (!Schema::hasColumn('custom_layouts', 'feedback_share_background_color')) {
                $table->string('feedback_share_background_color', 20)->default('#1a1b1f');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_layouts', function (Blueprint $table) {
            $columns = [
                'feedback_page_page_background',
                'feedback_page_background',
                'feedback_page_form_background',
                'feedback_page_title_color',
                'feedback_page_text_color',
                'feedback_share_title_color',
                'feedback_share_background_color',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('custom_layouts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}; 