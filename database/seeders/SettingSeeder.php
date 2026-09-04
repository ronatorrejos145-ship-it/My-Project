<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'company_name', 'value' => 'ISP Broadband & WiFi Services', 'group' => 'general', 'type' => 'string', 'description' => 'Legal ISP Company Name'],
            ['key' => 'currency', 'value' => 'PHP', 'group' => 'financial', 'type' => 'string', 'description' => 'Base Currency Symbol/Code'],
            ['key' => 'support_phone', 'value' => '+63 (02) 8000-1234', 'group' => 'general', 'type' => 'string', 'description' => 'Customer Support Hotline'],
            ['key' => 'support_email', 'value' => 'support@isp.example.com', 'group' => 'general', 'type' => 'string', 'description' => 'Customer Support Email'],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}
