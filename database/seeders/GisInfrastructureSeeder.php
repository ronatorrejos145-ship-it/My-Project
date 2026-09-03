<?php

namespace Database\Seeders;

use App\Models\NetworkTower;
use App\Models\DistributionPoint;
use App\Models\NetworkNode;
use App\Models\ServiceArea;
use App\Models\LocationHistory;
use Illuminate\Database\Seeder;

class GisInfrastructureSeeder extends Seeder
{
    public function run(): void
    {
        $node = NetworkNode::first();
        $serviceArea = ServiceArea::first();

        // Towers
        $tower = NetworkTower::firstOrCreate(
            ['code' => 'TWR-QC-01'],
            [
                'name' => 'Quezon City Central Tower [DEMO]',
                'tower_type' => 'ROOFTOP',
                'height_meters' => 45.00,
                'owner' => 'COMPANY',
                'latitude' => 14.6507000,
                'longitude' => 121.0300000,
                'service_area_id' => $serviceArea?->id,
                'status' => 'ACTIVE',
                'notes' => 'Primary 45m Rooftop Tower with sector antennas',
            ]
        );

        // Distribution Points
        $dp = DistributionPoint::firstOrCreate(
            ['code' => 'DP-FIBER-QC-01'],
            [
                'name' => 'Diliman Cabinet #1 1:16 Splitter [DEMO]',
                'dp_type' => 'FIBER_SPLITTER',
                'capacity' => 16,
                'parent_node_id' => $node?->id,
                'latitude' => 14.6530000,
                'longitude' => 121.0330000,
                'status' => 'ACTIVE',
            ]
        );

        // Update Service Area with GeoJSON Polygon boundary
        if ($serviceArea) {
            $serviceArea->update([
                'color_code' => '#3B82F6',
                'boundary_geojson' => [
                    'type' => 'Polygon',
                    'coordinates' => [
                        [
                            [121.0200, 14.6450],
                            [121.0450, 14.6450],
                            [121.0450, 14.6600],
                            [121.0200, 14.6600],
                            [121.0200, 14.6450],
                        ]
                    ]
                ]
            ]);
        }

        // Location History Record
        LocationHistory::create([
            'entity_type' => 'NetworkNode',
            'entity_id' => $node?->id ?? 1,
            'previous_latitude' => 14.6500000,
            'previous_longitude' => 121.0290000,
            'new_latitude' => 14.6507000,
            'new_longitude' => 121.0300000,
            'reason' => 'GPS re-survey precision adjustment',
        ]);
    }
}
