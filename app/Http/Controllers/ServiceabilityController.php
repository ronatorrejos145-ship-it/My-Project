<?php

namespace App\Http\Controllers;

use App\Models\ServicePackage;
use App\Models\ServiceabilityCheck;
use App\Http\Requests\CheckServiceabilityRequest;
use App\Http\Requests\OverrideServiceabilityRequest;
use App\Services\ServiceabilityEngineService;
use App\Services\ApplicationStatusService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceabilityController extends Controller
{
    protected ServiceabilityEngineService $engine;
    protected ApplicationStatusService $statusService;
    protected AuditLogService $auditLogService;

    public function __construct(
        ServiceabilityEngineService $engine,
        ApplicationStatusService $statusService,
        AuditLogService $auditLogService
    ) {
        $this->engine = $engine;
        $this->statusService = $statusService;
        $this->auditLogService = $auditLogService;
    }

    public function checkForm()
    {
        $packages = ServicePackage::where('status', 'ACTIVE')->get();
        return view('admin.serviceability.check', compact('packages'));
    }

    public function check(CheckServiceabilityRequest $request)
    {
        $package = ServicePackage::findOrFail($request->service_package_id);

        $result = $this->engine->evaluate(
            (float)$request->latitude,
            (float)$request->longitude,
            $package,
            $request->service_area_id
        );

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'result_status' => $result->result_status,
                'reason_code' => $result->reason_code,
                'explanation' => $result->explanation,
                'calculated_distance_meters' => $result->calculated_distance_meters,
            ]);
        }

        return redirect()->back()->with('serviceability_result', $result);
    }

    public function override(OverrideServiceabilityRequest $request, ServiceabilityCheck $check)
    {
        $updated = $this->statusService->overrideServiceability(
            $check,
            $request->override_status,
            $request->override_reason
        );

        $this->auditLogService->log(
            'SERVICEABILITY_OVERRIDE',
            'ServiceabilityChecks',
            $check->id,
            ['result_status' => $check->result_status],
            ['override_status' => $request->override_status, 'reason' => $request->override_reason]
        );

        return redirect()->back()->with('success', "Serviceability result overridden to {$request->override_status}.");
    }
}
