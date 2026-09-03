<?php

namespace App\Services;

use App\Models\InstallationStatusHistory;
use App\Models\InstallationWorkOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InstallationArrivalService
{
    public function recordArrival(
        InstallationWorkOrder $workOrder,
        float $latitude,
        float $longitude,
        ?float $accuracy = null,
        bool $allowOverride = false,
        ?string $overrideReason = null,
        ?int $userId = null
    ): InstallationWorkOrder {
        return DB::transaction(function () use ($workOrder, $latitude, $longitude, $accuracy, $allowOverride, $overrideReason, $userId) {
            $workOrder = InstallationWorkOrder::where('id', $workOrder->id)->lockForUpdate()->firstOrFail();

            $maxRadiusMeters = config('app.installation_arrival_radius_meters', 300);
            $distanceMeters = null;

            if ($workOrder->latitude && $workOrder->longitude) {
                $distanceMeters = $this->calculateDistanceMeters(
                    (float) $workOrder->latitude,
                    (float) $workOrder->longitude,
                    $latitude,
                    $longitude
                );

                if ($distanceMeters > $maxRadiusMeters && !$allowOverride) {
                    throw new InvalidArgumentException(sprintf(
                        "Technician arrival GPS is %.2fm away from installation location, exceeding maximum allowed radius of %dm. Override required.",
                        $distanceMeters,
                        $maxRadiusMeters
                    ));
                }
            }

            $oldStatus = $workOrder->status;
            $newStatus = 'ON_SITE';

            $workOrder->update([
                'arrived_at' => now(),
                'status' => $newStatus,
                'updated_by' => $userId,
            ]);

            $notes = sprintf("Arrival verified via GPS (Lat: %f, Lon: %f, Accuracy: %s)", $latitude, $longitude, $accuracy ? "{$accuracy}m" : 'N/A');
            if ($distanceMeters !== null) {
                $notes .= sprintf(" Distance from target: %.2fm", $distanceMeters);
            }
            if ($allowOverride && $overrideReason) {
                $notes .= " [OVERRIDE: {$overrideReason}]";
            }

            InstallationStatusHistory::create([
                'installation_id' => $workOrder->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $userId,
                'reason' => 'Technician arrived on site',
                'notes' => $notes,
            ]);

            AuditLogService::log(
                'TECHNICIAN_ARRIVED',
                'installations',
                $workOrder,
                ['status' => $oldStatus],
                ['status' => $newStatus, 'arrived_at' => now()->toIso8601String(), 'distance_meters' => $distanceMeters]
            );

            return $workOrder;
        });
    }

    private function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
