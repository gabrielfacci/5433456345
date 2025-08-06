<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create([
            'site_name' => 'BR Pro',
            'site_description' => 'Plataforma de apostas',
            'logo' => null,
            'favicon' => null,
            'email' => 'contato@brpro.com',
            'phone' => null,
            'address' => null,
            'facebook' => null,
            'twitter' => null,
            'instagram' => null,
            'youtube' => null,
            'tiktok' => null,
            'telegram' => null,
            'whatsapp' => null,
            'meta_keywords' => null,
            'meta_description' => null,
            'footer_text' => '© 2024 BR Pro. Todos os direitos reservados.',
            'maintenance_mode' => false,
            'maintenance_message' => 'Site em manutenção. Voltaremos em breve!',
            'software_background' => null,
            'software_logo_black2' => null,
        ]);
    }
} 