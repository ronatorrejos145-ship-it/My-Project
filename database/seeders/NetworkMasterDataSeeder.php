<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ServiceArea;
use App\Models\NetworkNode;
use App\Models\AccessPoint;
use App\Models\NetworkDevice;
use Illuminate\Database\Seeder;

class NetworkMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();
        $sa = ServiceArea::first();

        // Core Node
        $coreNode = NetworkNode::firstOrCreate(
            ['node_code' => 'NODE-CORE-01'],
            [
                'name' => 'EDSA Telecom Tower Core POP [DEMO]',
                'node_type' => 'CORE',
                'status' => 'ACTIVE',
                'branch_id' => $branch?->id,
                'service_area_id' => $sa?->id,
                'latitude' => 14.6507000,
                'longitude' => 121.0300000,
                'address' => 'EDSA Tower Rooftop, Quezon City',
                'description' => 'Main Fiber Core Gateway Node',
                'installation_date' => '2023-01-15',
            ]
        );

        // Access Node
        $accessNode = NetworkNode::firstOrCreate(
            ['node_code' => 'NODE-ACC-01'],
            [
                'name' => 'Diliman Sector Hub 1 [DEMO]',
                'node_type' => 'ACCESS',
                'status' => 'ACTIVE',
                'branch_id' => $branch?->id,
                'service_area_id' => $sa?->id,
                'parent_node_id' => $coreNode->id,
                'latitude' => 14.6540000,
                'longitude' => 121.0350000,
                'address' => 'Diliman Telecom Cabinet #4',
                'description' => 'Distribution and Wireless Sector Node',
                'installation_date' => '2023-03-20',
            ]
        );

        // MikroTik Core Router Device
        $coreRouter = NetworkDevice::firstOrCreate(
            ['device_code' => 'DEV-MK-01'],
            [
                'device_name' => 'Core CCR2116 Gateway [DEMO]',
                'device_type' => 'MIKROTIK',
                'hostname' => 'core-router-01.apexfiber.demo',
                'management_ip' => '10.0.0.1',
                'mac_address' => '6C:3B:6B:11:22:33',
                'serial_number' => 'MK-SN-2023-8899',
                'manufacturer' => 'MikroTik',
                'model' => 'CCR2116-12G-4S+',
                'firmware_version' => '7.12.1',
                'node_id' => $coreNode->id,
                'status' => 'ACTIVE',
                'capacity' => 1000,
            ]
        );

        // Access Point / NanoBox Device
        $apDevice = NetworkDevice::firstOrCreate(
            ['device_code' => 'DEV-AP-01'],
            [
                'device_name' => 'Sector 5G AP-01 [DEMO]',
                'device_type' => 'ACCESS_POINT',
                'hostname' => 'ap-diliman-01.apexfiber.demo',
                'management_ip' => '10.0.1.10',
                'mac_address' => '74:83:C2:44:55:66',
                'serial_number' => 'UB-SN-998877',
                'manufacturer' => 'Ubiquiti / NanoBox',
                'model' => 'airMAX Rocket AC Lite / NanoBox Sector',
                'node_id' => $accessNode->id,
                'parent_device_id' => $coreRouter->id,
                'status' => 'ACTIVE',
                'capacity' => 60,
            ]
        );

        // Access Point record
        AccessPoint::firstOrCreate(
            ['code' => 'AP-DILIMAN-5G-1'],
            [
                'node_id' => $accessNode->id,
                'network_device_id' => $apDevice->id,
                'name' => 'Diliman Sector A 5GHz AP',
                'technology' => '5GHz 802.11ac',
                'frequency' => '5180MHz',
                'ssid' => 'ApexBroadband_SectorA',
                'latitude' => 14.6541000,
                'longitude' => 121.0352000,
                'status' => 'ACTIVE',
                'capacity' => 60,
            ]
        );
    }
}
