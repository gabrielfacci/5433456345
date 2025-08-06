<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->boolean('featured_section_1')->default(false);
            $table->boolean('featured_section_2')->default(false);
            $table->boolean('featured_section_3')->default(false);
            $table->boolean('featured_section_4')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'featured_section_1',
                'featured_section_2',
                'featured_section_3',
                'featured_section_4'
            ]);
        });
    }
}; 