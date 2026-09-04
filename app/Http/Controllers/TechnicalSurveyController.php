<?php

namespace App\Http\Controllers;

use App\Models\TechnicalSurvey;
use App\Models\ServiceApplication;
use App\Models\Employee;
use App\Models\Item;
use App\Models\AssetModel;
use App\Http\Requests\StoreTechnicalSurveyRequest;
use App\Http\Requests\AssignTechnicalSurveyRequest;
use App\Http\Requests\SubmitTechnicalSurveyRequest;
use App\Services\TechnicalSurveyService;
use App\Services\TechnicalSurveyEvaluationService;
use App\Services\SurveyReportPdfService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TechnicalSurveyController extends Controller
{
    protected TechnicalSurveyService $surveyService;
    protected TechnicalSurveyEvaluationService $evaluationService;
    protected SurveyReportPdfService $pdfService;
    protected AuditLogService $auditLogService;

    public function __construct(
        TechnicalSurveyService $surveyService,
        TechnicalSurveyEvaluationService $evaluationService,
        SurveyReportPdfService $pdfService,
        AuditLogService $auditLogService
    ) {
        $this->surveyService = $surveyService;
        $this->evaluationService = $evaluationService;
        $this->pdfService = $pdfService;
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', TechnicalSurvey::class);

        $surveys = TechnicalSurvey::with(['application', 'technician.user', 'package'])
            ->when($request->search, function ($query, $search) {
                $query->where('survey_number', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15);

        $technicians = Employee::where('employment_status', 'ACTIVE')->get();

        return view('admin.technical-surveys.index', compact('surveys', 'technicians'));
    }

    public function store(StoreTechnicalSurveyRequest $request)
    {
        $application = ServiceApplication::findOrFail($request->application_id);

        $survey = $this->surveyService->createSurveyForApplication(
            $application,
            $request->technician_id,
            $request->priority
        );

        $this->auditLogService->log(
            'TECHNICAL_SURVEY_CREATE',
            'TechnicalSurveys',
            $survey->id,
            null,
            $survey->toArray()
        );

        return redirect()->route('admin.technical-surveys.show', $survey)
            ->with('success', "Technical Survey #{$survey->survey_number} created.");
    }

    public function show(TechnicalSurvey $survey)
    {
        Gate::authorize('view', $survey);

        $survey->load([
            'application.installationAddress',
            'customer',
            'package',
            'technician.user',
            'supervisor.user',
            'measurements',
            'photos',
            'materials.item',
            'equipment.assetModel',
            'statusHistories.user',
        ]);

        $items = Item::where('status', 'ACTIVE')->get();
        $assetModels = AssetModel::where('status', 'ACTIVE')->get();

        return view('admin.technical-surveys.show', compact('survey', 'items', 'assetModels'));
    }

    public function assign(AssignTechnicalSurveyRequest $request, TechnicalSurvey $survey)
    {
        $survey->update([
            'technician_id' => $request->technician_id,
            'scheduled_at' => $request->scheduled_at ?: now(),
            'status' => 'ASSIGNED',
        ]);

        return redirect()->route('admin.technical-surveys.show', $survey)
            ->with('success', 'Technician dispatched successfully.');
    }

    public function verifyGps(Request $request, TechnicalSurvey $survey)
    {
        Gate::authorize('submit', $survey);

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
        ]);

        $this->surveyService->verifyGpsArrival(
            $survey,
            (float)$request->latitude,
            (float)$request->longitude,
            (float)$request->accuracy
        );

        return redirect()->route('admin.technical-surveys.show', $survey)
            ->with('success', 'GPS arrival coordinates verified on site.');
    }

    public function uploadPhoto(Request $request, TechnicalSurvey $survey)
    {
        Gate::authorize('submit', $survey);

        $request->validate([
            'photo' => 'required|file|mimes:jpg,jpeg,png|max:10240',
            'category' => 'required|string',
            'caption' => 'nullable|string',
        ]);

        $this->surveyService->storeSurveyPhoto(
            $survey,
            $request->file('photo'),
            $request->category,
            $request->caption
        );

        return redirect()->route('admin.technical-surveys.show', $survey)
            ->with('success', 'Field site photo uploaded.');
    }

    public function submit(SubmitTechnicalSurveyRequest $request, TechnicalSurvey $survey)
    {
        $validated = $request->validated();

        $survey->update([
            'line_of_sight_status' => $validated['line_of_sight_status'],
            'installation_complexity' => $validated['installation_complexity'],
            'safety_assessment' => $validated['safety_assessment'],
            'technical_summary' => $validated['technical_summary'],
            'status' => 'PENDING_TECHNICAL_REVIEW',
            'submitted_at' => now(),
        ]);

        // Evaluate automated recommendation
        $eval = $this->evaluationService->evaluateSurvey($survey);
        $survey->update([
            'technical_recommendation' => $eval['recommendation'],
            'final_decision' => $eval['final_decision'],
        ]);

        return redirect()->route('admin.technical-surveys.show', $survey)
            ->with('success', 'Technical survey submitted for supervisor review.');
    }

    public function downloadReport(TechnicalSurvey $survey)
    {
        Gate::authorize('view', $survey);

        $html = $this->pdfService->generateReportHtml($survey);

        return response($html)->header('Content-Type', 'text/html');
    }
}
