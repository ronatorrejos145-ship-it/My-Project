<?php

namespace App\Services;

use App\Models\ServiceApplication;
use App\Models\ServiceApplicationStatusHistory;
use App\Models\ServiceabilityCheck;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ApplicationStatusService
{
    protected CustomerActivityService $activityService;

    public function __construct(CustomerActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Transition application status with immutable history and activity logging.
     */
    public function transition(ServiceApplication $application, string $newStatus, string $reason, ?string $notes = null): ServiceApplication
    {
        return DB::transaction(function () use ($application, $newStatus, $reason, $notes) {
            $oldStatus = $application->status;

            if ($oldStatus === $newStatus) {
                return $application;
            }

            $application->status = $newStatus;

            if ($newStatus === 'UNDER_REVIEW') {
                $application->reviewed_at = now();
                $application->reviewed_by = Auth::id();
            } elseif ($newStatus === 'APPROVED') {
                $application->approved_at = now();
                $application->approved_by = Auth::id();
            } elseif ($newStatus === 'REJECTED') {
                $application->rejected_at = now();
                $application->rejected_by = Auth::id();
                $application->rejection_reason = $reason;
            }

            $application->save();

            ServiceApplicationStatusHistory::create([
                'application_id' => $application->id,
                'previous_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $reason,
                'notes' => $notes,
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);

            if ($application->customer_id) {
                $this->activityService->log(
                    $application->customer_id,
                    'APPLICATION_UPDATED',
                    "Application #{$application->application_number} Status: {$newStatus}",
                    "Status transitioned from {$oldStatus} to {$newStatus}. Reason: {$reason}"
                );
            }

            return $application;
        });
    }

    /**
     * Supervisor override for automated serviceability evaluation results.
     */
    public function overrideServiceability(ServiceabilityCheck $check, string $overrideStatus, string $overrideReason): ServiceabilityCheck
    {
        return DB::transaction(function () use ($check, $overrideStatus, $overrideReason) {
            $check->is_overridden = true;
            $check->override_result_status = $overrideStatus;
            $check->override_reason = $overrideReason;
            $check->overridden_by = Auth::id();
            $check->overridden_at = now();
            $check->save();

            if ($check->application) {
                if ($overrideStatus === 'SERVICEABLE') {
                    $this->transition($check->application, 'UNDER_REVIEW', "Serviceability overridden to SERVICEABLE by supervisor: {$overrideReason}");
                } elseif ($overrideStatus === 'REQUIRES_TECHNICAL_SURVEY') {
                    $this->transition($check->application, 'REQUIRES_SURVEY', "Serviceability overridden to REQUIRES_SURVEY by supervisor: {$overrideReason}");
                }
            }

            return $check;
        });
    }
}
