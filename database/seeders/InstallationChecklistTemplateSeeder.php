<?php

namespace Database\Seeders;

use App\Models\InstallationChecklistSection;
use App\Models\InstallationChecklistTemplate;
use Illuminate\Database\Seeder;

class InstallationChecklistTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $template = InstallationChecklistTemplate::firstOrCreate(
            ['code' => 'NEW_INSTALL_STD'],
            [
                'name' => 'Standard ISP Fiber / Wireless New Installation Checklist',
                'work_type' => 'NEW_INSTALLATION',
                'description' => 'Comprehensive operational checklist for new residential & business ISP client installations.',
                'is_active' => true,
            ]
        );

        $sec1 = InstallationChecklistSection::firstOrCreate(
            ['template_id' => $template->id, 'title' => '1. Customer & Site Identity Verification'],
            ['sort_order' => 1]
        );

        $sec2 = InstallationChecklistSection::firstOrCreate(
            ['template_id' => $template->id, 'title' => '2. Physical Installation & Equipment Mounting'],
            ['sort_order' => 2]
        );

        $sec3 = InstallationChecklistSection::firstOrCreate(
            ['template_id' => $template->id, 'title' => '3. Router / CPE Configuration & WiFi Security'],
            ['sort_order' => 3]
        );

        $items = [
            [$sec1->id, 'CUST_ID_VERIFIED', 'Verify Customer Identity & Installation Address', 'YES_NO', true, 1],
            [$sec1->id, 'GPS_LOCATION_VERIFIED', 'Verify GPS Location and Site Access', 'YES_NO', true, 2],
            [$sec2->id, 'ROUTER_MOUNTED', 'Router/CPE Securely Mounted', 'YES_NO', true, 3],
            [$sec2->id, 'CABLE_ROUTED', 'Drop Cable Cleanly Routed and Secured with Clips', 'YES_NO', true, 4],
            [$sec2->id, 'POWER_GROUNDED', 'Power Outlet and Grounding Verified', 'YES_NO', true, 5],
            [$sec3->id, 'WIFI_SSID_CONFIGURED', 'Custom WiFi SSID and WPA2/WPA3 Password Configured', 'YES_NO', true, 6],
            [$sec3->id, 'MGMT_PASS_CHANGED', 'Default Management Admin Password Updated', 'YES_NO', true, 7],
        ];

        foreach ($items as [$secId, $code, $label, $type, $req, $sort]) {
            $sec = InstallationChecklistSection::find($secId);
            $sec->items()->firstOrCreate(
                ['item_code' => $code],
                [
                    'label' => $label,
                    'response_type' => $type,
                    'is_required' => $req,
                    'sort_order' => $sort,
                    'is_active' => true,
                ]
            );
        }
    }
}
