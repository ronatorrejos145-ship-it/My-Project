<?php

namespace App\Http\Controllers;

use App\Models\ServiceApplication;
use App\Models\ServicePackage;
use App\Models\Branch;
use App\Models\ServiceArea;
use App\Http\Requests\StoreServiceApplicationRequest;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Services\ServiceApplicationService;
use App\Services\ApplicationStatusService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceApplicationController extends Controller
{
    protected ServiceApplicationService $applicationService;
    protected ApplicationStatusService $statusService;
    protected AuditLogService $auditLogService;

    public function __construct(
        ServiceApplicationService $applicationService,
        ApplicationStatusService $statusService,
        AuditLogService $auditLogService
    ) {
        $this->applicationService = $applicationService;
        $this->statusService = $statusService;
        $this->auditLogService = $auditLogService;
    }

    public function wizard()
    {
        $packages = ServicePackage::where('status', 'ACTIVE')->where('public_visibility', true)->get();
        $serviceAreas = ServiceArea::where('status', 'ACTIVE')->get();

        return view('public.applications.wizard', compact('packages', 'serviceAreas'));
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', ServiceApplication::class);

        $applications = ServiceApplication::with(['package', 'branch', 'latestServiceabilityCheck'])
            ->when($request->search, function ($query, $search) {
                $query->where('application_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('primary_phone', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15);

        return view('admin.applications.index', compact('applications'));
    }

    public function store(StoreServiceApplicationRequest $request)
    {
        $application = $this->applicationService->submitApplication($request->validated());

        $this->auditLogService->log(
            'SERVICE_APPLICATION_SUBMIT',
            'ServiceApplications',
            $application->id,
            null,
            $application->toArray()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'application_number' => $application->application_number,
                'application_status' => $application->status,
                'message' => 'Your internet service application has been submitted successfully.',
            ]);
        }

        return redirect()->route('admin.applications.show', $application)
            ->with('success', "Service Application #{$application->application_number} submitted successfully.");
    }

    public function show(ServiceApplication $application)
    {
        Gate::authorize('view', $application);

        $application->load([
            'package',
            'packageVersion',
            'branch',
            'serviceArea',
            'installationAddress',
            'statusHistories.user',
            'serviceabilityChecks.nearestNode',
            'serviceabilityChecks.nearestAccessPoint',
            'serviceabilityChecks.overrider',
            'documents',
            'customer',
            'lead',
            'reviewer',
            'approver',
        ]);

        return view('admin.applications.show', compact('application'));
    }

    public function updateStatus(UpdateApplicationStatusRequest $request, ServiceApplication $application)
    {
        $this->statusService->transition(
            $application,
            $request->status,
            $request->reason,
            $request->notes
        );

        return redirect()->route('admin.applications.show', $application)
            ->with('success', "Application status updated to {$request->status}.");
    }
}
