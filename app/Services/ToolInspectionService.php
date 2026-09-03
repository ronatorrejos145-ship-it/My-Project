<?php

namespace App\Services;

use App\Models\Tool;
use App\Models\ToolInspection;
use Illuminate\Support\Facades\DB;

class ToolInspectionService
{
    public function recordInspection(Tool $tool, string $result = 'PASS', string $condition = 'GOOD', ?string $notes = null, ?int $userId = null): ToolInspection
    {
        return DB::transaction(function () use ($tool, $result, $condition, $notes, $userId) {
            $tool = Tool::where('id', $tool->id)->lockForUpdate()->firstOrFail();

            $inspection = ToolInspection::create([
                'tool_id' => $tool->id,
                'inspector_id' => $userId,
                'inspected_at' => now(),
                'result' => $result,
                'condition' => $condition,
                'notes' => $notes,
            ]);

            if ($condition !== $tool->condition) {
                $tool->update(['condition' => $condition]);
            }

            if ($result === 'REPAIR_REQUIRED') {
                $tool->update(['status' => 'IN_REPAIR']);
            }

            AuditLogService::log(
                'INSPECT_TOOL',
                'tools',
                $inspection,
                null,
                $inspection->toArray()
            );

            return $inspection;
        });
    }
}
