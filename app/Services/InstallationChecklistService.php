<?php

namespace App\Services;

use App\Models\InstallationChecklistItem;
use App\Models\InstallationChecklistResponse;
use App\Models\InstallationChecklistTemplate;
use App\Models\InstallationWorkOrder;
use Illuminate\Support\Facades\DB;

class InstallationChecklistService
{
    public function getDefaultTemplate(string $workType = 'NEW_INSTALLATION'): ?InstallationChecklistTemplate
    {
        return InstallationChecklistTemplate::with(['sections.items'])
            ->where('is_active', true)
            ->where(function ($q) use ($workType) {
                $q->where('work_type', $workType)->orWhereNull('work_type');
            })
            ->first();
    }

    public function recordResponse(
        InstallationWorkOrder $workOrder,
        int $checklistItemId,
        mixed $value,
        ?bool $isPassed = null,
        ?string $notes = null,
        ?int $userId = null
    ): InstallationChecklistResponse {
        return DB::transaction(function () use ($workOrder, $checklistItemId, $value, $isPassed, $notes, $userId) {
            $item = InstallationChecklistItem::findOrFail($checklistItemId);

            $boolVal = null;
            $textVal = null;

            if (is_bool($value)) {
                $boolVal = $value;
                $textVal = $value ? 'YES' : 'NO';
            } elseif (is_numeric($value) || is_string($value)) {
                $textVal = (string) $value;
                if (in_array(strtoupper($textVal), ['YES', 'PASS', 'TRUE', '1'])) {
                    $boolVal = true;
                } elseif (in_array(strtoupper($textVal), ['NO', 'FAIL', 'FALSE', '0'])) {
                    $boolVal = false;
                }
            }

            if ($isPassed === null && $boolVal !== null) {
                $isPassed = $boolVal;
            }

            $response = InstallationChecklistResponse::updateOrCreate(
                [
                    'installation_id' => $workOrder->id,
                    'checklist_item_id' => $item->id,
                ],
                [
                    'response_value' => $textVal,
                    'response_bool' => $boolVal,
                    'is_passed' => $isPassed,
                    'notes' => $notes,
                    'completed_by' => $userId,
                    'completed_at' => now(),
                ]
            );

            // Update work order status to IN_PROGRESS if currently ON_SITE
            if ($workOrder->status === 'ON_SITE') {
                $workOrder->update(['status' => 'IN_PROGRESS']);
            }

            return $response;
        });
    }

    public function checkCompletionStatus(InstallationWorkOrder $workOrder): array
    {
        $template = $this->getDefaultTemplate($workOrder->work_type);
        if (!$template) {
            return ['is_complete' => true, 'missing_items' => []];
        }

        $requiredItems = InstallationChecklistItem::whereIn('section_id', $template->sections->pluck('id'))
            ->where('is_required', true)
            ->where('is_active', true)
            ->get();

        $completedItemIds = InstallationChecklistResponse::where('installation_id', $workOrder->id)
            ->whereNotNull('response_value')
            ->pluck('checklist_item_id')
            ->toArray();

        $missing = [];
        foreach ($requiredItems as $item) {
            if (!in_array($item->id, $completedItemIds)) {
                $missing[] = $item->label;
            }
        }

        return [
            'is_complete' => count($missing) === 0,
            'missing_items' => $missing,
        ];
    }
}
