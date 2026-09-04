<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderFailure;
use App\Models\WorkOrderRevisit;
use App\Models\WorkOrderDowntime;
use App\Models\WorkOrderMaterial;
use Illuminate\Support\Facades\DB;

class WorkOrderReportService
{
    public function getExecutiveMetrics(?string $startDate = null, ?string $endDate = null): array
    {
        $query = WorkOrder::query();
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $total = (clone $query)->count();
        $completed = (clone $query)->whereIn('status', ['COMPLETED', 'CLOSED'])->count();
        $open = (clone $query)->whereNotIn('status', ['COMPLETED', 'CLOSED', 'CANCELLED'])->count();
        $failed = (clone $query)->where('status', 'FAILED')->count();
        $cancelled = (clone $query)->where('status', 'CANCELLED')->count();

        // Revisit count
        $revisitCount = WorkOrderRevisit::count();

        // First time fix rate calculation
        $firstTimeFixRate = $total > 0 ? round((($completed - $revisitCount) / max($total, 1)) * 100, 2) : 100.0;

        // SLA breach rate
        $slaBreached = (clone $query)->where('is_sla_breached', true)->count();
        $slaComplianceRate = $total > 0 ? round((( $total - $slaBreached ) / max($total, 1)) * 100, 2) : 100.0;

        // Total downtime minutes
        $totalDowntimeMinutes = WorkOrderDowntime::sum('duration_minutes');

        // Material cost total
        $totalMaterialCost = WorkOrderMaterial::sum('total_cost');

        return [
            'total_work_orders' => $total,
            'completed' => $completed,
            'open' => $open,
            'failed' => $failed,
            'cancelled' => $cancelled,
            'revisit_count' => $revisitCount,
            'first_time_fix_rate' => max(0, $firstTimeFixRate),
            'sla_breached' => $slaBreached,
            'sla_compliance_rate' => max(0, $slaComplianceRate),
            'total_downtime_minutes' => $totalDowntimeMinutes,
            'total_material_cost' => $totalMaterialCost,
        ];
    }
}
