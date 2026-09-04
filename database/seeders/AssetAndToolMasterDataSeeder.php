<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\AssetModel;
use App\Models\Asset;
use App\Models\ToolCategory;
use App\Models\Tool;
use Illuminate\Database\Seeder;

class AssetAndToolMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Asset Categories
        $catRouter = AssetCategory::firstOrCreate(['code' => 'CAT-ROUTER'], ['name' => 'WiFi Routers & Modems', 'description' => 'Customer Premises Equipment & Routers']);
        $catONU = AssetCategory::firstOrCreate(['code' => 'CAT-ONU'], ['name' => 'GPON ONU / ONT', 'description' => 'Fiber Optical Network Units']);
        $catSwitch = AssetCategory::firstOrCreate(['code' => 'CAT-SWITCH'], ['name' => 'Network Switches', 'description' => 'Managed and Unmanaged Switches']);

        // Asset Models
        $modelFiber = AssetModel::firstOrCreate(
            ['category_id' => $catONU->id, 'model_name' => 'HG8145V5 Dual Band GPON'],
            [
                'manufacturer' => 'Huawei',
                'model_number' => 'HG8145V5',
                'description' => 'Dual Band WiFi GPON ONT Unit',
                'warranty_period_months' => 12,
            ]
        );

        // Assets
        Asset::firstOrCreate(
            ['asset_tag' => 'AST-ONU-0001'],
            [
                'asset_category_id' => $catONU->id,
                'asset_model_id' => $modelFiber->id,
                'serial_number' => '4857544321AABBCC',
                'mac_address' => '00:E0:4C:11:22:33',
                'manufacturer' => 'Huawei',
                'purchase_date' => '2024-01-10',
                'purchase_cost' => 1850.00,
                'warranty_start' => '2024-01-10',
                'warranty_end' => '2025-01-10',
                'current_status' => 'AVAILABLE',
                'current_location' => 'Main Warehouse',
                'condition' => 'NEW',
            ]
        );

        // Tool Categories
        $toolCatFiber = ToolCategory::firstOrCreate(['code' => 'TCAT-FIBER'], ['name' => 'Fiber Tools', 'description' => 'Optical Splicers and OTDR Meters']);
        $toolCatHand = ToolCategory::firstOrCreate(['code' => 'TCAT-HAND'], ['name' => 'Installation Hand Tools', 'description' => 'Crimpers, Strippers, Drills']);

        // Tools
        Tool::firstOrCreate(
            ['tool_code' => 'TL-SPLICE-01'],
            [
                'category_id' => $toolCatFiber->id,
                'name' => 'Signal Fire AI-9 Fusion Splicer [DEMO]',
                'manufacturer' => 'Signal Fire',
                'model' => 'AI-9',
                'serial_number' => 'SF-SPLICE-8899',
                'condition' => 'GOOD',
                'status' => 'AVAILABLE',
                'purchase_date' => '2023-06-15',
                'location' => 'Tech Equipment Storage A',
            ]
        );
    }
}
