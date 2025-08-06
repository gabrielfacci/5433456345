<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRewardColumnsToMissionsTable extends Migration
{
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->string('reward_type')->default('money')->after('target_amount');
            $table->decimal('reward_spins', 8, 2)->nullable()->after('reward_type');
        });
    }

    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn(['reward_type', 'reward_spins']);
        });
    }
}
