<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderTimeEntry;
use Illuminate\Support\Facades\DB;

class WorkOrderTimeTrackingService
{
    public function logTimeEntry(WorkOrder $workOrder, int $technicianId, string $entryType, string $startAt, ?string $endAt = null, ?string $notes = null): WorkOrderTimeEntry
    {
        $start = \Carbon\Carbon::parse($startAt);
        $end = $endAt ? \Carbon\Carbon::parse($endAt) : null;
        $durationMinutes = $end ? $start->diffInMinutes($end) : 0;

        return WorkOrderTimeEntry::create([
            'work_order_id' => $workOrder->id,
            'technician_id' => $technicianId,
            'entry_type' => $entryType,
            'start_at' => $start,
            'end_at' => $end,
            'duration_minutes' => $durationMinutes,
            'notes' => $notes,
        ]);
    }
}
