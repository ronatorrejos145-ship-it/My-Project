<?php

namespace App\Services;

use App\Models\InstallationWorkOrder;
use App\Models\ServiceApplication;
use App\Models\TechnicalSurvey;
use App\Models\InstallationStatusHistory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InstallationCreationService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService
    ) {}

    public function createFromApprovedSurvey(int $technicalSurveyId, array $data = [], ?int $userId = null): InstallationWorkOrder
    {
        return DB::transaction(function () use ($technicalSurveyId, $data, $userId) {
            $survey = TechnicalSurvey::with(['application', 'customer', 'package', 'packageVersion', 'serviceArea', 'installationAddress', 'installationLocation'])
                ->where('id', $technicalSurveyId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($survey->approval_status !== 'APPROVED') {
                throw new InvalidArgumentException("Cannot create installation: Technical Survey {$survey->survey_number} is not approved.");
            }

            if (!$survey->application || $survey->application->status !== 'APPROVED') {
                throw new InvalidArgumentException("Cannot create installation: Service Application is not approved.");
            }

            // Check duplicate active installation work order
            $existing = InstallationWorkOrder::where('application_id', $survey->application_id)
                ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
                ->first();

            if ($existing) {
                throw new InvalidArgumentException("An active installation work order ({$existing->work_order_number}) already exists for this application.");
            }

            $workOrderNumber = $this->numberSequenceService->getNextNumber('INSTALLATION');

            $workOrder = InstallationWorkOrder::create([
                'work_order_number' => $workOrderNumber,
                'application_id' => $survey->application_id,
                'customer_id' => $survey->customer_id,
                'technical_survey_id' => $survey->id,
                'package_id' => $survey->package_id,
                'package_version_id' => $survey->package_version_id,
                'branch_id' => $survey->application->branch_id,
                'service_area_id' => $survey->service_area_id,
                'installation_address_id' => $survey->installation_address_id,
                'installation_location_id' => $survey->installation_location_id,
                'latitude' => $survey->latitude ?? $survey->installationLocation?->latitude,
                'longitude' => $survey->longitude ?? $survey->installationLocation?->longitude,
                'gps_accuracy' => $survey->gps_accuracy ?? $survey->installationLocation?->accuracy_meters,
                'work_type' => $data['work_type'] ?? 'NEW_INSTALLATION',
                'priority' => $data['priority'] ?? 'NORMAL',
                'source' => $data['source'] ?? 'SYSTEM',
                'reason' => $data['reason'] ?? null,
                'requested_date' => $data['requested_date'] ?? now()->toDateString(),
                'target_date' => $data['target_date'] ?? now()->addDays(2)->toDateString(),
                'status' => 'PENDING',
                'notes' => $data['notes'] ?? $survey->technical_notes,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // Update application and survey status
            $survey->application->update(['status' => 'PENDING_INSTALLATION']);
            $survey->update(['status' => 'READY_FOR_INSTALLATION']);

            // Record status history
            InstallationStatusHistory::create([
                'installation_id' => $workOrder->id,
                'old_status' => null,
                'new_status' => 'PENDING',
                'changed_by' => $userId,
                'reason' => 'Work order created from approved survey ' . $survey->survey_number,
            ]);

            AuditLogService::log(
                'CREATE_INSTALLATION',
                'installations',
                $workOrder,
                null,
                $workOrder->toArray()
            );

            return $workOrder;
        });
    }
}
