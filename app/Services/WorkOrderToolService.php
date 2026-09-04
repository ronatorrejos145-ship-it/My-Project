<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderTool;
use App\Models\Tool;
use App\Services\ToolCheckoutService;
use Illuminate\Support\Facades\DB;

class WorkOrderToolService
{
    public function checkoutTool(WorkOrder $workOrder, int $toolId, int $technicianId, ?string $conditionBefore = 'GOOD'): WorkOrderTool
    {
        return DB::transaction(function () use ($workOrder, $toolId, $technicianId, $conditionBefore) {
            $toolRecord = WorkOrderTool::create([
                'work_order_id' => $workOrder->id,
                'tool_id' => $toolId,
                'technician_id' => $technicianId,
                'checked_out_at' => now(),
                'condition_before' => $conditionBefore,
            ]);

            // Sync with tool state
            $tool = Tool::find($toolId);
            if ($tool) {
                $tool->status = 'IN_USE';
                $tool->save();
            }

            return $toolRecord;
        });
    }

    public function checkinTool(WorkOrderTool $workOrderTool, ?string $conditionAfter = 'GOOD', bool $isDamaged = false, ?string $damageNotes = null): WorkOrderTool
    {
        return DB::transaction(function () use ($workOrderTool, $conditionAfter, $isDamaged, $damageNotes) {
            $workOrderTool->checked_in_at = now();
            $workOrderTool->condition_after = $conditionAfter;
            $workOrderTool->is_damaged = $isDamaged;
            $workOrderTool->damage_notes = $damageNotes;
            $workOrderTool->save();

            $tool = Tool::find($workOrderTool->tool_id);
            if ($tool) {
                $tool->status = $isDamaged ? 'DAMAGED' : 'AVAILABLE';
                $tool->save();
            }

            return $workOrderTool;
        });
    }
}
