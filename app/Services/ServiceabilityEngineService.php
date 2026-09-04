<?php

namespace App\Services;

use App\Models\ServicePackage;
use App\Models\ServiceArea;
use App\Models\NetworkNode;
use App\Models\AccessPoint;
use App\Models\NetworkDevice;
use App\Models\ServiceabilityCheck;
use Illuminate\Support\Facades\Auth;

class ServiceabilityEngineService
{
    /**
     * Engine version tracking for auditability.
     */
    public const ENGINE_VERSION = '1.0.0';

    /**
     * Calculate precise distance between two GPS coordinates using the Haversine formula (meters).
     */
    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusMeters = 6371000.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusMeters * $c, 2);
    }

    /**
     * Evaluate both Commercial Availability and Technical Serviceability for a proposed GPS location and package.
     */
    public function evaluate(float $latitude, float $longitude, ServicePackage $package, ?int $serviceAreaId = null, ?int $applicationId = null, ?int $customerId = null): ServiceabilityCheck
    {
        // 1. Evaluate Commercial Availability
        if ($package->status !== 'ACTIVE') {
            return $this->recordCheck($package, $latitude, $longitude, $serviceAreaId, 'PACKAGE_UNAVAILABLE', 'PKG_INACTIVE', 'The requested package is currently inactive or discontinued.', null, null, null, null, 'CAPACITY_UNKNOWN', $applicationId, $customerId);
        }

        $activeVersion = $package->activeVersion ?: $package->versions->first();
        if (!$activeVersion) {
            return $this->recordCheck($package, $latitude, $longitude, $serviceAreaId, 'PACKAGE_UNAVAILABLE', 'NO_ACTIVE_VERSION', 'No active pricing version exists for this package.', null, null, null, null, 'CAPACITY_UNKNOWN', $applicationId, $customerId);
        }

        // 2. Evaluate Service Area Match
        $matchedArea = null;
        if ($serviceAreaId) {
            $matchedArea = ServiceArea::find($serviceAreaId);
        }

        // If package requires specific service area, verify association
        if ($package->serviceAreas()->count() > 0 && $matchedArea) {
            if (!$package->serviceAreas()->where('service_areas.id', $matchedArea->id)->exists()) {
                return $this->recordCheck($package, $latitude, $longitude, $matchedArea?->id, 'PACKAGE_UNAVAILABLE', 'AREA_NOT_ELIGIBLE', 'This package is not commercially available in the selected service area.', null, null, null, null, 'CAPACITY_UNKNOWN', $applicationId, $customerId);
            }
        }

        // 3. Find Nearest Network Infrastructure using Haversine GPS Distance
        $nodes = NetworkNode::whereNotNull('latitude')->whereNotNull('longitude')->get();
        $nearestNode = null;
        $minNodeDistance = null;

        foreach ($nodes as $node) {
            $dist = $this->calculateHaversineDistance($latitude, $longitude, (float)$node->latitude, (float)$node->longitude);
            if ($minNodeDistance === null || $dist < $minNodeDistance) {
                $minNodeDistance = $dist;
                $nearestNode = $node;
            }
        }

        $accessPoints = AccessPoint::whereNotNull('latitude')->whereNotNull('longitude')->get();
        $nearestAP = null;
        $minAPDistance = null;

        foreach ($accessPoints as $ap) {
            $dist = $this->calculateHaversineDistance($latitude, $longitude, (float)$ap->latitude, (float)$ap->longitude);
            if ($minAPDistance === null || $dist < $minAPDistance) {
                $minAPDistance = $dist;
                $nearestAP = $ap;
            }
        }

        $nanoboxes = NetworkDevice::where('device_type', 'NANOBOX')->whereNotNull('latitude')->whereNotNull('longitude')->get();
        $nearestNanobox = null;
        $minNanoboxDistance = null;

        foreach ($nanoboxes as $box) {
            $dist = $this->calculateHaversineDistance($latitude, $longitude, (float)$box->latitude, (float)$box->longitude);
            if ($minNanoboxDistance === null || $dist < $minNanoboxDistance) {
                $minNanoboxDistance = $dist;
                $nearestNanobox = $box;
            }
        }

        // 4. Apply Technology-Specific Distance & Capacity Rules
        $technology = strtoupper($package->technology);

        if ($technology === 'FIBER' || $technology === 'FTTH' || $technology === 'FTTB') {
            $maxDistanceMeters = 1000.0; // 1km standard fiber drop box distance
            if ($minNodeDistance !== null && $minNodeDistance <= $maxDistanceMeters) {
                return $this->recordCheck(
                    $package, $latitude, $longitude, $matchedArea?->id,
                    'SERVICEABLE', 'FIBER_NODE_IN_RANGE',
                    "Location is within {$minNodeDistance}m of Fiber Node '{$nearestNode->name}'.",
                    $nearestNode?->id, $nearestAP?->id, $nearestNanobox?->id, $minNodeDistance, 'CAPACITY_AVAILABLE', $applicationId, $customerId
                );
            } elseif ($minNodeDistance !== null && $minNodeDistance <= 2500.0) {
                return $this->recordCheck(
                    $package, $latitude, $longitude, $matchedArea?->id,
                    'REQUIRES_TECHNICAL_SURVEY', 'FIBER_EXTENSION_REQUIRED',
                    "Location is {$minNodeDistance}m from nearest Fiber Node. Field technical survey required to estimate cable drop.",
                    $nearestNode?->id, $nearestAP?->id, $nearestNanobox?->id, $minNodeDistance, 'CAPACITY_UNKNOWN', $applicationId, $customerId
                );
            }
        } elseif ($technology === 'WIRELESS' || $technology === 'RADIO' || $technology === 'HOTSPOT') {
            $maxDistanceMeters = 3000.0; // 3km wireless line of sight limit
            if ($minAPDistance !== null && $minAPDistance <= $maxDistanceMeters) {
                return $this->recordCheck(
                    $package, $latitude, $longitude, $matchedArea?->id,
                    'SERVICEABLE', 'WIRELESS_AP_IN_RANGE',
                    "Location is within {$minAPDistance}m of Sector AP '{$nearestAP->name}'. Line-of-sight required.",
                    $nearestNode?->id, $nearestAP?->id, $nearestNanobox?->id, $minAPDistance, 'CAPACITY_AVAILABLE', $applicationId, $customerId
                );
            }
        }

        // 5. Default Fallback
        if ($nearestNode || $nearestAP || $nearestNanobox) {
            $shortestDist = min(array_filter([$minNodeDistance, $minAPDistance, $minNanoboxDistance], fn($v) => $v !== null));

            if ($shortestDist > 5000.0) {
                return $this->recordCheck(
                    $package, $latitude, $longitude, $matchedArea?->id,
                    'OUT_OF_COVERAGE', 'DISTANCE_EXCEEDED',
                    "Location is too far ({$shortestDist}m) from network infrastructure.",
                    $nearestNode?->id, $nearestAP?->id, $nearestNanobox?->id, $shortestDist, 'CAPACITY_UNKNOWN', $applicationId, $customerId
                );
            }

            return $this->recordCheck(
                $package, $latitude, $longitude, $matchedArea?->id,
                'REQUIRES_TECHNICAL_SURVEY', 'LINE_OF_SIGHT_CHECK',
                "Nearest node is {$shortestDist}m away. On-site technical survey required to verify feasibility.",
                $nearestNode?->id, $nearestAP?->id, $nearestNanobox?->id, $shortestDist, 'CAPACITY_UNKNOWN', $applicationId, $customerId
            );
        }

        return $this->recordCheck(
            $package, $latitude, $longitude, $matchedArea?->id,
            'OUT_OF_COVERAGE', 'NO_NEARBY_INFRASTRUCTURE',
            'No active network nodes or access points were found near the provided GPS location.',
            null, null, null, null, 'CAPACITY_UNKNOWN', $applicationId, $customerId
        );
    }

    protected function recordCheck(
        ServicePackage $package, float $lat, float $lon, ?int $serviceAreaId,
        string $resultStatus, string $reasonCode, string $explanation,
        ?int $nodeId, ?int $apId, ?int $nanoboxId, ?float $distanceMeters, string $capacityStatus,
        ?int $applicationId, ?int $customerId
    ): ServiceabilityCheck {
        return ServiceabilityCheck::create([
            'application_id' => $applicationId,
            'customer_id' => $customerId,
            'package_id' => $package->id,
            'package_version_id' => $package->activeVersion?->id ?: $package->versions->first()?->id,
            'latitude' => $lat,
            'longitude' => $lon,
            'service_area_id' => $serviceAreaId,
            'result_status' => $resultStatus,
            'reason_code' => $reasonCode,
            'explanation' => $explanation,
            'nearest_node_id' => $nodeId,
            'nearest_access_point_id' => $apId,
            'nearest_nanobox_id' => $nanoboxId,
            'calculated_distance_meters' => $distanceMeters,
            'capacity_status' => $capacityStatus,
            'checked_at' => now(),
            'checked_by' => Auth::id(),
            'engine_version' => self::ENGINE_VERSION,
        ]);
    }
}
