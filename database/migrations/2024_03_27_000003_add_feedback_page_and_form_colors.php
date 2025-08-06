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
            $table->string('feedback_page_page_background', 20)->default('#12131d');
            $table->string('feedback_page_form_background', 20)->default('#1E2024');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_layouts', function (Blueprint $table) {
            $table->dropColumn('feedback_page_page_background');
            $table->dropColumn('feedback_page_form_background');
        });
    }
}; 