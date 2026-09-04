<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\ServiceApplication;
use App\Models\NetworkNode;
use App\Models\AccessPoint;
use App\Models\NetworkDevice;
use App\Models\NetworkTower;
use App\Models\DistributionPoint;
use App\Models\ServiceArea;

class GisService
{
    /**
     * Efficiently fetch map entities inside a viewport bounding box (North, South, East, West).
     */
    public function getViewportMapData(float $north, float $south, float $east, float $west, array $layers = []): array
    {
        $result = [
            'nodes' => [],
            'access_points' => [],
            'nanoboxes' => [],
            'towers' => [],
            'distribution_points' => [],
            'customers' => [],
            'applications' => [],
            'service_areas' => [],
        ];

        // 1. Network Nodes
        if (empty($layers) || in-array('nodes', $layers)) {
            $result['nodes'] = NetworkNode::whereBetween('latitude', [$south, $north])
                ->whereBetween('longitude', [$west, $east])
                ->get(['id', 'node_code', 'name', 'node_type', 'status', 'latitude', 'longitude']);
        }

        // 2. Access Points
        if (empty($layers) || in-array('access_points', $layers)) {
            $result['access_points'] = AccessPoint::whereBetween('latitude', [$south, $north])
                ->whereBetween('longitude', [$west, $east])
                ->get(['id', 'code', 'name', 'technology', 'status', 'latitude', 'longitude']);
        }

        // 3. NanoBoxes
        if (empty($layers) || in-array('nanoboxes', $layers)) {
            $result['nanoboxes'] = NetworkDevice::where('device_type', 'NANOBOX')
                ->whereBetween('latitude', [$south, $north])
                ->whereBetween('longitude', [$west, $east])
                ->get(['id', 'device_code', 'device_name', 'status', 'latitude', 'longitude']);
        }

        // 4. Towers
        if (empty($layers) || in-array('towers', $layers)) {
            $result['towers'] = NetworkTower::whereBetween('latitude', [$south, $north])
                ->whereBetween('longitude', [$west, $east])
                ->get(['id', 'code', 'name', 'tower_type', 'height_meters', 'status', 'latitude', 'longitude']);
        }

        // 5. Distribution Points
        if (empty($layers) || in-array('distribution_points', $layers)) {
            $result['distribution_points'] = DistributionPoint::whereBetween('latitude', [$south, $north])
                ->whereBetween('longitude', [$west, $east])
                ->get(['id', 'code', 'name', 'dp_type', 'capacity', 'status', 'latitude', 'longitude']);
        }

        // 6. Customers
        if (empty($layers) || in-array('customers', $layers)) {
            $result['customers'] = Customer::whereNotNull('primary_address_id')
                ->whereHas('primaryAddress', function ($q) use ($north, $south, $east, $west) {
                    $q->whereBetween('latitude', [$south, $north])
                        ->whereBetween('longitude', [$west, $east]);
                })
                ->take(500)
                ->get(['id', 'customer_number', 'account_number', 'first_name', 'last_name', 'business_name', 'customer_type', 'status']);
        }

        // 7. Applications
        if (empty($layers) || in-array('applications', $layers)) {
            $result['applications'] = ServiceApplication::whereBetween('latitude', [$south, $north])
                ->whereBetween('longitude', [$west, $east])
                ->take(200)
                ->get(['id', 'application_number', 'first_name', 'last_name', 'business_name', 'status', 'latitude', 'longitude']);
        }

        // 8. Service Area Polygons
        if (empty($layers) || in-array('service_areas', $layers)) {
            $result['service_areas'] = ServiceArea::whereNotNull('boundary_geojson')
                ->get(['id', 'code', 'name', 'color_code', 'boundary_geojson', 'status']);
        }

        return $result;
    }
}
