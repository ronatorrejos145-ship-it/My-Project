<?php

namespace App\Services;

use App\Models\InstallationWorkOrder;

class InstallationCompletionValidationService
{
    public function __construct(protected InstallationChecklistService $checklistService) {}

    public function validate(InstallationWorkOrder $workOrder): array
    {
        $errors = [];

        // 1. Check required checklist items
        $checklistStatus = $this->checklistService->checkCompletionStatus($workOrder);
        if (!$checklistStatus['is_complete']) {
            $errors[] = "Incomplete checklist items: " . implode(', ', $checklistStatus['missing_items']);
        }

        // 2. Check required photos
        $photoCount = $workOrder->photos()->count();
        if ($photoCount === 0) {
            $errors[] = "At least one installation photo is required.";
        }

        // 3. Check required equipment
        $equipmentCount = $workOrder->equipment()->count();
        if ($equipmentCount === 0) {
            $errors[] = "At least one installed equipment asset must be assigned.";
        }

        // 4. Check required technical tests
        $testCount = $workOrder->tests()->count();
        if ($testCount === 0) {
            $errors[] = "Technical tests (connectivity/speed) must be recorded.";
        }

        // 5. Check customer acceptance
        $acceptance = $workOrder->acceptances()->whereIn('acceptance_status', ['ACCEPTED', 'ACCEPTED_WITH_ISSUES'])->first();
        if (!$acceptance && !$workOrder->accepted_at) {
            $errors[] = "Customer acceptance signature is missing.";
        }

        // 6. Check GPS coordinates
        if (!$workOrder->latitude || !$workOrder->longitude) {
            $errors[] = "Installation GPS coordinates are missing.";
        }

        return [
            'ready' => count($errors) === 0,
            'errors' => $errors,
        ];
    }
}
