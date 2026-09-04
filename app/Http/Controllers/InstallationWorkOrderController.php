<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\InstallationWorkOrder;
use App\Models\TechnicalSurvey;
use App\Services\InstallationAcceptanceService;
use App\Services\InstallationArrivalService;
use App\Services\InstallationAssignmentService;
use App\Services\InstallationChecklistService;
use App\Services\InstallationCompletionService;
use App\Services\InstallationCreationService;
use App\Services\InstallationDispatchService;
use App\Services\InstallationEquipmentService;
use App\Services\InstallationMaterialService;
use App\Services\InstallationReportPdfService;
use App\Services\InstallationSchedulingService;
use App\Services\InstallationSupervisorReviewService;
use App\Services\InstallationTestService;
use Illuminate\Http\Request;

class InstallationWorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = InstallationWorkOrder::with(['customer', 'package', 'assignedTechnician', 'branch'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('work_order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('customer_number', 'like', "%{$search}%");
                  });
            });
        }

        $installations = $query->paginate(15);

        return view('admin.installations.index', compact('installations'));
    }

    public function create()
    {
        $approvedSurveys = TechnicalSurvey::with(['customer', 'package', 'application'])
            ->where('approval_status', 'APPROVED')
            ->get();

        return view('admin.installations.create', compact('approvedSurveys'));
    }

    public function store(Request $request, InstallationCreationService $creationService)
    {
        $validated = $request->validate([
            'technical_survey_id' => 'required|exists:technical_surveys,id',
            'work_type' => 'nullable|string',
            'priority' => 'nullable|string',
            'requested_date' => 'nullable|date',
            'target_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $workOrder = $creationService->createFromApprovedSurvey(
            $validated['technical_survey_id'],
            $validated,
            auth()->id()
        );

        return redirect()->route('admin.installations.show', $workOrder)
            ->with('success', "Installation Work Order {$workOrder->work_order_number} created successfully.");
    }

    public function show(InstallationWorkOrder $installation, InstallationChecklistService $checklistService)
    {
        $installation->load([
            'customer',
            'application',
            'technicalSurvey',
            'package',
            'packageVersion',
            'assignedTechnician',
            'supervisor',
            'statusHistories.user',
            'assignments.newTechnician',
            'schedules',
            'checklistResponses.item',
            'photos',
            'materials.item',
            'equipment.asset',
            'tests',
            'acceptances',
            'supervisorReviews.supervisor',
            'installationNotes.author',
            'handoff',
        ]);

        $checklistTemplate = $checklistService->getDefaultTemplate($installation->work_type);
        $technicians = Employee::where('employment_status', 'ACTIVE')->get();

        return view('admin.installations.show', compact('installation', 'checklistTemplate', 'technicians'));
    }

    public function assign(Request $request, InstallationWorkOrder $installation, InstallationAssignmentService $assignmentService)
    {
        $validated = $request->validate([
            'technician_id' => 'required|exists:employees,id',
            'team' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $assignmentService->assignTechnician(
            $installation,
            $validated['technician_id'],
            $validated['team'] ?? null,
            null,
            $validated['reason'] ?? null,
            auth()->id()
        );

        return back()->with('success', 'Technician assigned successfully.');
    }

    public function schedule(Request $request, InstallationWorkOrder $installation, InstallationSchedulingService $schedulingService)
    {
        $validated = $request->validate([
            'scheduled_date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'allow_override' => 'nullable|boolean',
            'override_reason' => 'nullable|string',
        ]);

        $schedulingService->scheduleInstallation(
            $installation,
            $validated['scheduled_date'],
            $validated['start_time'],
            $validated['end_time'],
            $installation->assigned_technician_id,
            $request->boolean('allow_override'),
            $validated['override_reason'] ?? null,
            auth()->id()
        );

        return back()->with('success', 'Installation scheduled successfully.');
    }

    public function complete(Request $request, InstallationWorkOrder $installation, InstallationCompletionService $completionService)
    {
        $completionService->completeInstallation(
            $installation,
            auth()->id(),
            $request->boolean('bypass_validation'),
            $request->input('bypass_reason')
        );

        return back()->with('success', 'Installation work order successfully completed and handed off for activation.');
    }

    public function reviewSupervisor(Request $request, InstallationWorkOrder $installation, InstallationSupervisorReviewService $reviewService)
    {
        $validated = $request->validate([
            'supervisor_id' => 'required|exists:employees,id',
            'decision' => 'required|in:APPROVE,RETURN_FOR_REWORK,FAIL',
            'comments' => 'nullable|string',
        ]);

        $reviewService->submitReview(
            $installation,
            $validated['supervisor_id'],
            $validated['decision'],
            $validated['comments'] ?? null,
            auth()->id()
        );

        return back()->with('success', 'Supervisor review recorded successfully.');
    }

    public function downloadPdf(InstallationWorkOrder $installation, InstallationReportPdfService $pdfService)
    {
        $html = $pdfService->generatePdfHtml($installation);

        return response($html)
            ->header('Content-Type', 'text/html'); // Print-friendly HTML view
    }
}
