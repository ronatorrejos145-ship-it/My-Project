<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class WarehouseMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();

        // Warehouse
        $wh = Warehouse::firstOrCreate(
            ['code' => 'WH-MAIN-MNL'],
            [
                'name' => 'Main Central Warehouse [DEMO]',
                'branch_id' => $branch?->id,
                'address' => '100 Telecom Tower Logistics Center, Quezon City',
                'status' => 'ACTIVE',
            ]
        );

        // Warehouse Storage Locations
        WarehouseLocation::firstOrCreate(
            ['warehouse_id' => $wh->id, 'code' => 'RACK-A1'],
            ['name' => 'Rack A1 - Routers & ONTs', 'aisle' => 'Aisle 1', 'rack' => 'Rack A', 'shelf' => 'Shelf 1']
        );

        WarehouseLocation::firstOrCreate(
            ['warehouse_id' => $wh->id, 'code' => 'BIN-CABLE-01'],
            ['name' => 'Cable Bin 1 - Fiber Drop Cable', 'aisle' => 'Aisle 2', 'bin' => 'Bin 01']
        );

        // Item Categories
        $catCable = ItemCategory::firstOrCreate(['code' => 'ICAT-CABLE'], ['name' => 'Fiber & LAN Cables']);
        $catConnector = ItemCategory::firstOrCreate(['code' => 'ICAT-CONN'], ['name' => 'Connectors & Accessories']);

        // Supplier
        $supplier = Supplier::firstOrCreate(
            ['supplier_code' => 'SUPP-OPTICS-PH'],
            [
                'legal_name' => 'OpticTech Philippines Supplies Inc. [DEMO]',
                'trade_name' => 'OpticTech Supplies',
                'contact_person' => 'Juan Dela Cruz',
                'phone' => '+63 2 8999 1122',
                'email' => 'sales@optictech.demo',
                'address' => 'Pasig City, Metro Manila',
                'status' => 'ACTIVE',
            ]
        );

        // Items
        $itemCable = Item::firstOrCreate(
            ['sku' => 'SKU-FIBER-1CORE-1000M'],
            [
                'item_code' => 'ITEM-FIBER-DROP-1C',
                'name' => '1-Core Outdoor Fiber Drop Cable (1000m Roll) [DEMO]',
                'category_id' => $catCable->id,
                'unit' => 'METERS',
                'description' => '1-Core FTTH Outdoor Drop Cable with FRP Wire',
                'unit_cost' => 3.50,
                'minimum_stock' => 5000,
                'reorder_level' => 10000,
                'default_supplier_id' => $supplier->id,
                'status' => 'ACTIVE',
            ]
        );

        $itemCable->suppliers()->syncWithoutDetaching([
            $supplier->id => [
                'supplier_item_code' => 'OPT-FC-1C-1K',
                'supplier_price' => 3.20,
                'lead_time_days' => 5,
            ]
        ]);
    }
}
