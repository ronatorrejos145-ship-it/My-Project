<?php

namespace App\Services;

use App\Models\TechnicalSurvey;
use App\Models\TechnicalSurveyStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SurveyApprovalService
{
    protected ApplicationStatusService $appStatusService;
    protected CustomerActivityService $activityService;

    public function __construct(ApplicationStatusService $appStatusService, CustomerActivityService $activityService)
    {
        $this->appStatusService = $appStatusService;
        $this->activityService = $activityService;
    }

    /**
     * Technical supervisor review and decision (APPROVE, REJECT, REQUEST_RESURVEY).
     */
    public function reviewSurvey(TechnicalSurvey $survey, string $decision, ?string $reason = null, ?string $notes = null): TechnicalSurvey
    {
        return DB::transaction(function () use ($survey, $decision, $reason, $notes) {
            $oldStatus = $survey->status;

            if ($decision === 'APPROVED') {
                $survey->status = 'APPROVED';
                $survey->final_decision = 'TECHNICALLY_FEASIBLE';
                $survey->approved_at = now();
                $survey->supervisor_id = Auth::user()?->employee?->id ?: $survey->supervisor_id;

                // Update application handoff status to APPROVED / READY_FOR_INSTALLATION
                if ($survey->application) {
                    $this->appStatusService->transition(
                        $survey->application,
                        'APPROVED',
                        "Technical Survey #{$survey->survey_number} approved as technically feasible."
                    );
                }
            } elseif ($decision === 'REJECTED') {
                $survey->status = 'REJECTED';
                $survey->final_decision = 'NOT_FEASIBLE';
                $survey->rejected_at = now();
                $survey->rejection_reason = $reason;

                if ($survey->application) {
                    $this->appStatusService->transition(
                        $survey->application,
                        'REJECTED',
                        "Technical Survey #{$survey->survey_number} rejected: {$reason}"
                    );
                }
            } elseif ($decision === 'REQUEST_RESURVEY') {
                $survey->status = 'RESURVEY_REQUIRED';
                $survey->resurvey_requested_at = now();
            }

            $survey->reviewed_at = now();
            $survey->save();

            TechnicalSurveyStatusHistory::create([
                'survey_id' => $survey->id,
                'previous_status' => $oldStatus,
                'new_status' => $survey->status,
                'reason' => $reason ?: "Supervisor decision: {$decision}",
                'notes' => $notes,
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);

            if ($survey->customer_id) {
                $this->activityService->log(
                    $survey->customer_id,
                    'SURVEY_COMPLETED',
                    "Technical Survey #{$survey->survey_number} Decision: {$decision}",
                    "Field site technical survey decision: {$decision}. Reason: {$reason}"
                );
            }

            return $survey;
        });
    }
}
