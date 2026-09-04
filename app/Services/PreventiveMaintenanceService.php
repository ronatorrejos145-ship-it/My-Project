<?php

namespace App\Services;

use App\Models\MaintenancePlan;
use App\Models\MaintenancePlanSchedule;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class PreventiveMaintenanceService
{
    public function __construct(
        protected WorkOrderService $workOrderService
    ) {}

    public function generateScheduledWorkOrders(): int
    {
        $dueSchedules = MaintenancePlanSchedule::with('plan')
            ->where('status', 'ACTIVE')
            ->where('auto_generate_wo', true)
            ->where('next_due_at', '<=', now())
            ->get();

        $generatedCount = 0;

        foreach ($dueSchedules as $schedule) {
            DB::transaction(function () use ($schedule, &$generatedCount) {
                $plan = $schedule->plan;

                // Create preventive work order
                $wo = $this->workOrderService->createWorkOrder([
                    'maintenance_plan_schedule_id' => $schedule->id,
                    'customer_id' => $schedule->customer_id,
                    'subscription_id' => $schedule->subscription_id,
                    'asset_id' => $schedule->asset_id,
                    'work_order_type' => 'PREVENTIVE',
                    'title' => 'Preventive Maintenance: ' . $plan->name,
                    'description' => $plan->description,
                    'priority' => 'NORMAL',
                    'severity' => 'MINOR',
                    'status' => 'PENDING',
                ]);

                // Calculate next due date based on frequency
                $schedule->last_run_at = now();
                $schedule->next_due_at = match ($plan->frequency) {
                    'DAILY' => now()->addDay(),
                    'WEEKLY' => now()->addWeek(),
                    'MONTHLY' => now()->addMonth(),
                    'QUARTERLY' => now()->addMonths(3),
                    'SEMI_ANNUAL' => now()->addMonths(6),
                    'ANNUAL' => now()->addYear(),
                    default => now()->addDays($plan->custom_interval_days ?? 30),
                };
                $schedule->save();

                $generatedCount++;
            });
        }

        return $generatedCount;
    }
}
