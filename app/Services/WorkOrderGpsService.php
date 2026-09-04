<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderGpsEvent;
use App\Models\TechnicianAvailability;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorkOrderGpsService
{
    public function __construct(
        protected WorkOrderStateService $stateService
    ) {}

    public function recordGpsEvent(WorkOrder $workOrder, int $technicianId, string $eventType, float $latitude, float $longitude, ?float $accuracy = null, ?string $deviceInfo = null): WorkOrderGpsEvent
    {
        // Anti-abuse coordinate validation
        $isFlagged = false;
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            throw new InvalidArgumentException("Invalid geographic coordinates.");
        }
        if ($latitude == 0.0 && $longitude == 0.0) {
            $isFlagged = true;
        }

        return DB::transaction(function () use ($workOrder, $technicianId, $eventType, $latitude, $longitude, $accuracy, $deviceInfo, $isFlagged) {
            $gpsEvent = WorkOrderGpsEvent::create([
                'work_order_id' => $workOrder->id,
                'technician_id' => $technicianId,
                'event_type' => $eventType,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'location_accuracy' => $accuracy,
                'device_info' => $deviceInfo,
                'is_flagged_suspicious' => $isFlagged,
            ]);

            // Update technician's current GPS location & status
            TechnicianAvailability::updateOrCreate(
                ['technician_id' => $technicianId],
                [
                    'current_latitude' => $latitude,
                    'current_longitude' => $longitude,
                    'last_gps_at' => now(),
                    'current_status' => match ($eventType) {
                        'TRAVEL_STARTED' => 'EN_ROUTE',
                        'ARRIVED', 'WORK_STARTED' => 'ON_SITE',
                        'WORK_PAUSED' => 'BUSY',
                        'COMPLETED', 'DEPARTED' => 'AVAILABLE',
                        default => 'AVAILABLE'
                    }
                ]
            );

            // Transition work order status based on event type
            if ($eventType === 'TRAVEL_STARTED' && in_array($workOrder->status, ['ASSIGNED', 'SCHEDULED'])) {
                $this->stateService->transition($workOrder, 'EN_ROUTE', $technicianId, 'GPS Event', 'En route to site');
            } elseif ($eventType === 'ARRIVED' && in_array($workOrder->status, ['EN_ROUTE', 'SCHEDULED', 'ASSIGNED'])) {
                $this->stateService->transition($workOrder, 'ON_SITE', $technicianId, 'GPS Event', 'Arrived on site');
            } elseif ($eventType === 'WORK_STARTED' && in_array($workOrder->status, ['ON_SITE', 'EN_ROUTE', 'ASSIGNED'])) {
                $this->stateService->transition($workOrder, 'IN_PROGRESS', $technicianId, 'GPS Event', 'Work started');
            }

            return $gpsEvent;
        });
    }
}
