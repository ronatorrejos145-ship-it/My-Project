<?php

namespace App\Services;

use App\Models\NetworkNode;
use App\Models\AccessPoint;
use App\Models\NetworkDevice;
use App\Models\NetworkTower;
use App\Models\DistributionPoint;

class NearbyInfrastructureService
{
    protected ServiceabilityEngineService $engine;

    public function __construct(ServiceabilityEngineService $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Find all network infrastructure items within a given radius (meters) of a target coordinate.
     */
    public function findNearbyInfrastructure(float $latitude, float $longitude, float $maxRadiusMeters = 3000.0): array
    {
        $nearby = [];

        // Nodes
        foreach (NetworkNode::whereNotNull('latitude')->whereNotNull('longitude')->get() as $node) {
            $dist = $this->engine->calculateHaversineDistance($latitude, $longitude, (float)$node->latitude, (float)$node->longitude);
            if ($dist <= $maxRadiusMeters) {
                $nearby[] = [
                    'type' => 'NETWORK_NODE',
                    'id' => $node->id,
                    'code' => $node->node_code,
                    'name' => $node->name,
                    'node_type' => $node->node_type,
                    'distance_meters' => $dist,
                    'latitude' => (float)$node->latitude,
                    'longitude' => (float)$node->longitude,
                ];
            }
        }

        // Access Points
        foreach (AccessPoint::whereNotNull('latitude')->whereNotNull('longitude')->get() as $ap) {
            $dist = $this->engine->calculateHaversineDistance($latitude, $longitude, (float)$ap->latitude, (float)$ap->longitude);
            if ($dist <= $maxRadiusMeters) {
                $nearby[] = [
                    'type' => 'ACCESS_POINT',
                    'id' => $ap->id,
                    'code' => $ap->code,
                    'name' => $ap->name,
                    'technology' => $ap->technology,
                    'distance_meters' => $dist,
                    'latitude' => (float)$ap->latitude,
                    'longitude' => (float)$ap->longitude,
                ];
            }
        }

        // Towers
        foreach (NetworkTower::whereNotNull('latitude')->whereNotNull('longitude')->get() as $tower) {
            $dist = $this->engine->calculateHaversineDistance($latitude, $longitude, (float)$tower->latitude, (float)$tower->longitude);
            if ($dist <= $maxRadiusMeters) {
                $nearby[] = [
                    'type' => 'TOWER',
                    'id' => $tower->id,
                    'code' => $tower->code,
                    'name' => $tower->name,
                    'height_meters' => (float)$tower->height_meters,
                    'distance_meters' => $dist,
                    'latitude' => (float)$tower->latitude,
                    'longitude' => (float)$tower->longitude,
                ];
            }
        }

        // Sort nearby by distance
        usort($nearby, fn($a, $b) => $a['distance_meters'] <=> $b['distance_meters']);

        return $nearby;
    }
}
