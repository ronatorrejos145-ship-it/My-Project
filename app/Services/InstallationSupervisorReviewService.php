<?php

namespace App\Services;

use App\Models\InstallationStatusHistory;
use App\Models\InstallationSupervisorReview;
use App\Models\InstallationWorkOrder;
use Illuminate\Support\Facades\DB;

class InstallationSupervisorReviewService
{
    public function submitReview(
        InstallationWorkOrder $workOrder,
        int $supervisorEmployeeId,
        string $decision, // APPROVE, RETURN_FOR_REWORK, FAIL
        ?string $comments = null,
        ?int $userId = null
    ): InstallationSupervisorReview {
        return DB::transaction(function () use ($workOrder, $supervisorEmployeeId, $decision, $comments, $userId) {
            $workOrder = InstallationWorkOrder::where('id', $workOrder->id)->lockForUpdate()->firstOrFail();

            $review = InstallationSupervisorReview::create([
                'installation_id' => $workOrder->id,
                'supervisor_id' => $supervisorEmployeeId,
                'decision' => $decision,
                'comments' => $comments,
                'reviewed_at' => now(),
            ]);

            $oldStatus = $workOrder->status;

            if ($decision === 'RETURN_FOR_REWORK') {
                $workOrder->update(['status' => 'IN_PROGRESS', 'updated_by' => $userId]);
                InstallationStatusHistory::create([
                    'installation_id' => $workOrder->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'IN_PROGRESS',
                    'changed_by' => $userId,
                    'reason' => 'Supervisor returned work order for rework: ' . $comments,
                ]);
            } elseif ($decision === 'FAIL') {
                $workOrder->update([
                    'status' => 'FAILED',
                    'failed_at' => now(),
                    'failure_reason' => 'Supervisor rejected work order: ' . $comments,
                    'updated_by' => $userId,
                ]);
                InstallationStatusHistory::create([
                    'installation_id' => $workOrder->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'FAILED',
                    'changed_by' => $userId,
                    'reason' => 'Supervisor failed work order: ' . $comments,
                ]);
            }

            AuditLogService::log(
                'SUPERVISOR_REVIEW_INSTALLATION',
                'installations',
                $review,
                null,
                $review->toArray()
            );

            return $review;
        });
    }
}
