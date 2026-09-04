<?php

namespace App\Services;

use App\Models\InstallationHandoff;
use App\Models\InstallationStatusHistory;
use App\Models\InstallationWorkOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InstallationCompletionService
{
    public function __construct(
        protected InstallationCompletionValidationService $validationService
    ) {}

    public function completeInstallation(InstallationWorkOrder $workOrder, ?int $userId = null, bool $bypassValidation = false, ?string $bypassReason = null): InstallationWorkOrder
    {
        return DB::transaction(function () use ($workOrder, $userId, $bypassValidation, $bypassReason) {
            /** @var InstallationWorkOrder $workOrder */
            $workOrder = InstallationWorkOrder::where('id', $workOrder->id)->lockForUpdate()->firstOrFail();

            if ($workOrder->status === 'COMPLETED') {
                throw new InvalidArgumentException("Installation work order {$workOrder->work_order_number} is already completed.");
            }

            if (!$bypassValidation) {
                $validation = $this->validationService->validate($workOrder);
                if (!$validation['ready']) {
                    throw new InvalidArgumentException("Installation completion validation failed: " . implode(' | ', $validation['errors']));
                }
            }

            $oldStatus = $workOrder->status;
            $newStatus = 'COMPLETED';

            $workOrder->update([
                'status' => $newStatus,
                'completed_at' => now(),
                'updated_by' => $userId,
            ]);

            // Update application status
            if ($workOrder->application) {
                $workOrder->application->update(['status' => 'APPROVED']);
            }

            // Create Handoff Record
            $handoff = InstallationHandoff::updateOrCreate(
                ['installation_id' => $workOrder->id],
                [
                    'customer_id' => $workOrder->customer_id,
                    'application_id' => $workOrder->application_id,
                    'technical_survey_id' => $workOrder->technical_survey_id,
                    'package_id' => $workOrder->package_id,
                    'package_version_id' => $workOrder->package_version_id,
                    'location_id' => $workOrder->installation_location_id,
                    'latitude' => $workOrder->latitude,
                    'longitude' => $workOrder->longitude,
                    'status' => 'READY_FOR_ACTIVATION',
                    'handover_data' => [
                        'work_order_number' => $workOrder->work_order_number,
                        'completed_at' => now()->toIso8601String(),
                        'equipment' => $workOrder->equipment()->get()->toArray(),
                        'tests' => $workOrder->tests()->get()->toArray(),
                    ],
                    'handoff_at' => now(),
                ]
            );

            InstallationStatusHistory::create([
                'installation_id' => $workOrder->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $userId,
                'reason' => 'Installation work order successfully completed and handed off for activation.',
                'notes' => $bypassValidation ? "Validation bypassed: {$bypassReason}" : null,
            ]);

            AuditLogService::log(
                'COMPLETE_INSTALLATION',
                'installations',
                $workOrder,
                ['status' => $oldStatus],
                ['status' => $newStatus, 'completed_at' => now()->toIso8601String(), 'handoff_id' => $handoff->id]
            );

            return $workOrder;
        });
    }
}
