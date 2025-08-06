<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'site_name')) {
                $table->string('site_name')->nullable();
            }
            if (!Schema::hasColumn('settings', 'site_description')) {
                $table->string('site_description')->nullable();
            }
            if (!Schema::hasColumn('settings', 'logo')) {
                $table->string('logo')->nullable();
            }
            if (!Schema::hasColumn('settings', 'favicon')) {
                $table->string('favicon')->nullable();
            }
            if (!Schema::hasColumn('settings', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('settings', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('settings', 'address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('settings', 'facebook')) {
                $table->string('facebook')->nullable();
            }
            if (!Schema::hasColumn('settings', 'twitter')) {
                $table->string('twitter')->nullable();
            }
            if (!Schema::hasColumn('settings', 'instagram')) {
                $table->string('instagram')->nullable();
            }
            if (!Schema::hasColumn('settings', 'youtube')) {
                $table->string('youtube')->nullable();
            }
            if (!Schema::hasColumn('settings', 'tiktok')) {
                $table->string('tiktok')->nullable();
            }
            if (!Schema::hasColumn('settings', 'telegram')) {
                $table->string('telegram')->nullable();
            }
            if (!Schema::hasColumn('settings', 'whatsapp')) {
                $table->string('whatsapp')->nullable();
            }
            if (!Schema::hasColumn('settings', 'meta_keywords')) {
                $table->text('meta_keywords')->nullable();
            }
            if (!Schema::hasColumn('settings', 'meta_description')) {
                $table->text('meta_description')->nullable();
            }
            if (!Schema::hasColumn('settings', 'footer_text')) {
                $table->text('footer_text')->nullable();
            }
            if (!Schema::hasColumn('settings', 'maintenance_mode')) {
                $table->boolean('maintenance_mode')->default(false);
            }
            if (!Schema::hasColumn('settings', 'maintenance_message')) {
                $table->text('maintenance_message')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'site_name',
                'site_description',
                'logo',
                'favicon',
                'email',
                'phone',
                'address',
                'facebook',
                'twitter',
                'instagram',
                'youtube',
                'tiktok',
                'telegram',
                'whatsapp',
                'meta_keywords',
                'meta_description',
                'footer_text',
                'maintenance_mode',
                'maintenance_message',
            ]);
        });
    }
}; 