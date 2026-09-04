<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderChecklistTemplate;
use App\Models\WorkOrderChecklistResult;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorkOrderChecklistService
{
    public function recordChecklistResults(WorkOrder $workOrder, int $templateId, array $itemsData, ?int $userId = null): array
    {
        $template = WorkOrderChecklistTemplate::with('items')->findOrFail($templateId);

        return DB::transaction(function () use ($workOrder, $template, $itemsData, $userId) {
            $results = [];

            foreach ($template->items as $item) {
                $val = $itemsData[$item->id] ?? null;

                if ($item->is_required && (is_null($val) || $val === '')) {
                    throw new InvalidArgumentException("Mandatory checklist item '{$item->item_label}' is missing.");
                }

                $isPassed = null;
                if ($item->item_type === 'CHECKBOX' || $item->item_type === 'YES_NO' || $item->item_type === 'PASS_FAIL') {
                    $isPassed = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                }

                $result = WorkOrderChecklistResult::updateOrCreate(
                    [
                        'work_order_id' => $workOrder->id,
                        'template_id' => $template->id,
                        'checklist_item_id' => $item->id,
                    ],
                    [
                        'result_value' => is_array($val) ? json_encode($val) : (string) $val,
                        'is_passed' => $isPassed,
                        'completed_by_user_id' => $userId,
                        'completed_at' => now(),
                    ]
                );

                $results[] = $result;
            }

            return $results;
        });
    }
}
