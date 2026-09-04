<?php

namespace App\Http\Controllers;

use App\Models\TechnicalSurvey;
use App\Http\Requests\ReviewTechnicalSurveyRequest;
use App\Services\SurveyApprovalService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TechnicalSurveyReviewController extends Controller
{
    protected SurveyApprovalService $approvalService;
    protected AuditLogService $auditLogService;

    public function __construct(SurveyApprovalService $approvalService, AuditLogService $auditLogService)
    {
        $this->approvalService = $approvalService;
        $this->auditLogService = $auditLogService;
    }

    public function reviewForm(TechnicalSurvey $survey)
    {
        Gate::authorize('review', $survey);

        $survey->load([
            'application.installationAddress',
            'customer',
            'package',
            'technician.user',
            'measurements',
            'photos',
            'materials.item',
            'equipment.assetModel',
        ]);

        return view('admin.technical-surveys.review', compact('survey'));
    }

    public function review(ReviewTechnicalSurveyRequest $request, TechnicalSurvey $survey)
    {
        $validated = $request->validated();

        $this->approvalService->reviewSurvey(
            $survey,
            $validated['decision'],
            $validated['reason'] ?? null,
            $validated['notes'] ?? null
        );

        $this->auditLogService->log(
            'TECHNICAL_SURVEY_REVIEW',
            'TechnicalSurveys',
            $survey->id,
            ['status' => $survey->getOriginal('status')],
            ['status' => $survey->status, 'decision' => $validated['decision']]
        );

        return redirect()->route('admin.technical-surveys.show', $survey)
            ->with('success', "Technical Survey review decision saved: {$validated['decision']}.");
    }
}
