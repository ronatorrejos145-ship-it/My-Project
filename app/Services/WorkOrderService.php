<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\MaintenanceRequest;
use App\Models\Ticket;
use App\Models\CustomerComplaint;
use App\Models\SupportIncident;
use App\Models\Customer;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkOrderService
{
    public function __construct(
        protected WorkOrderStateService $stateService
    ) {}

    public function generateWorkOrderNumber(): string
    {
        $year = date('Y');
        $prefix = "WO-{$year}-";

        return DB::transaction(function () use ($prefix) {
            $latest = WorkOrder::where('work_order_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            if ($latest) {
                $lastNumber = (int) substr($latest->work_order_number, -6);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            return $prefix . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
        });
    }

    public function createWorkOrder(array $data, ?int $createdByUserId = null): WorkOrder
    {
        return DB::transaction(function () use ($data, $createdByUserId) {
            $woNumber = $this->generateWorkOrderNumber();

            // Calculate automatic priority if not explicitly provided
            $priority = $data['priority'] ?? $this->calculatePriority($data);

            $workOrder = WorkOrder::create([
                'work_order_number' => $woNumber,
                'maintenance_request_id' => $data['maintenance_request_id'] ?? null,
                'ticket_id' => $data['ticket_id'] ?? null,
                'complaint_id' => $data['complaint_id'] ?? null,
                'incident_id' => $data['incident_id'] ?? null,
                'maintenance_plan_schedule_id' => $data['maintenance_plan_schedule_id'] ?? null,
                'parent_work_order_id' => $data['parent_work_order_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'subscription_id' => $data['subscription_id'] ?? null,
                'asset_id' => $data['asset_id'] ?? null,
                'work_order_type' => $data['work_order_type'] ?? 'CORRECTIVE',
                'status' => $data['status'] ?? 'PENDING',
                'priority' => $priority,
                'severity' => $data['severity'] ?? 'MODERATE',
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'symptoms' => $data['symptoms'] ?? null,
                'suspected_cause' => $data['suspected_cause'] ?? null,
                'service_address' => $data['service_address'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'location_accuracy' => $data['location_accuracy'] ?? null,
                'scheduled_start_at' => $data['scheduled_start_at'] ?? null,
                'scheduled_end_at' => $data['scheduled_end_at'] ?? null,
                'assigned_technician_id' => $data['assigned_technician_id'] ?? null,
                'supervisor_id' => $data['supervisor_id'] ?? null,
                'territory_code' => $data['territory_code'] ?? null,
                'is_billable' => $data['is_billable'] ?? false,
                'estimated_cost' => $data['estimated_cost'] ?? 0.00,
                'created_by_user_id' => $createdByUserId,
            ]);

            // Set SLA targets based on priority
            $this->applySlaTargets($workOrder);

            // Log initial status
            $this->stateService->transition($workOrder, $workOrder->status, $createdByUserId, 'Initial Creation', 'Work order created');

            return $workOrder;
        });
    }

    public function createFromTicket(Ticket $ticket, array $overrideData = [], ?int $createdByUserId = null): WorkOrder
    {
        $customer = $ticket->customer;
        $subscription = $ticket->subscription;

        $data = array_merge([
            'ticket_id' => $ticket->id,
            'customer_id' => $ticket->customer_id,
            'subscription_id' => $ticket->subscription_id,
            'work_order_type' => 'CORRECTIVE',
            'title' => 'Maintenance: ' . $ticket->subject,
            'description' => $ticket->description,
            'priority' => $ticket->priority ?? 'NORMAL',
            'severity' => 'MODERATE',
            'service_address' => $customer?->address ?? $customer?->installation_address,
            'latitude' => $customer?->gps_latitude,
            'longitude' => $customer?->gps_longitude,
        ], $overrideData);

        return $this->createWorkOrder($data, $createdByUserId);
    }

    public function createFromIncident(SupportIncident $incident, array $overrideData = [], ?int $createdByUserId = null): WorkOrder
    {
        $data = array_merge([
            'incident_id' => $incident->id,
            'work_order_type' => 'INCIDENT_RESPONSE',
            'title' => 'Incident Dispatch: ' . $incident->title,
            'description' => $incident->description,
            'priority' => $incident->severity === 'CRITICAL' ? 'CRITICAL' : 'URGENT',
            'severity' => $incident->severity ?? 'MAJOR',
        ], $overrideData);

        return $this->createWorkOrder($data, $createdByUserId);
    }

    protected function calculatePriority(array $data): string
    {
        if (!empty($data['incident_id'])) {
            return 'CRITICAL';
        }

        if (isset($data['severity']) && in_array($data['severity'], ['CRITICAL', 'MAJOR'])) {
            return 'URGENT';
        }

        return 'NORMAL';
    }

    protected function applySlaTargets(WorkOrder $workOrder): void
    {
        $now = now();
        $responseHours = 24;
        $restorationHours = 48;
        $resolutionHours = 72;

        switch ($workOrder->priority) {
            case 'CRITICAL':
                $responseHours = 1;
                $restorationHours = 4;
                $resolutionHours = 8;
                break;
            case 'URGENT':
                $responseHours = 2;
                $restorationHours = 8;
                $resolutionHours = 24;
                break;
            case 'HIGH':
                $responseHours = 4;
                $restorationHours = 12;
                $resolutionHours = 36;
                break;
            case 'NORMAL':
                $responseHours = 12;
                $restorationHours = 24;
                $resolutionHours = 48;
                break;
            case 'LOW':
                $responseHours = 24;
                $restorationHours = 48;
                $resolutionHours = 96;
                break;
        }

        $workOrder->response_due_at = $now->copy()->addHours($responseHours);
        $workOrder->restoration_due_at = $now->copy()->addHours($restorationHours);
        $workOrder->resolution_due_at = $now->copy()->addHours($resolutionHours);
        $workOrder->save();
    }
}
