<?php

namespace App\Http\Controllers;

use App\Models\ServiceApplication;
use App\Models\ServicePackage;
use App\Http\Requests\StoreServiceApplicationRequest;
use App\Http\Requests\CheckServiceabilityRequest;
use App\Services\ServiceApplicationService;
use App\Services\ServiceabilityEngineService;
use Illuminate\Http\Request;

class PublicApplicationApiController extends Controller
{
    protected ServiceApplicationService $applicationService;
    protected ServiceabilityEngineService $serviceabilityEngine;

    public function __construct(
        ServiceApplicationService $applicationService,
        ServiceabilityEngineService $serviceabilityEngine
    ) {
        $this->applicationService = $applicationService;
        $this->serviceabilityEngine = $serviceabilityEngine;
    }

    public function checkServiceability(CheckServiceabilityRequest $request)
    {
        $package = ServicePackage::findOrFail($request->service_package_id);

        $check = $this->serviceabilityEngine->evaluate(
            (float)$request->latitude,
            (float)$request->longitude,
            $package,
            $request->service_area_id
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'result_status' => $check->result_status,
                'reason_code' => $check->reason_code,
                'explanation' => $check->explanation,
                'calculated_distance_meters' => $check->calculated_distance_meters,
            ]
        ]);
    }

    public function submit(StoreServiceApplicationRequest $request)
    {
        $application = $this->applicationService->submitApplication($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Online customer service application submitted successfully.',
            'data' => [
                'application_number' => $application->application_number,
                'status' => $application->status,
                'submitted_at' => $application->submitted_at?->toIso8601String(),
            ]
        ]);
    }

    public function status(string $applicationNumber)
    {
        $app = ServiceApplication::where('application_number', $applicationNumber)->first();

        if (!$app) {
            return response()->json(['status' => 'error', 'message' => 'Application number not found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'application_number' => $app->application_number,
                'applicant_name' => $app->applicant_name,
                'status' => $app->status,
                'package_name' => $app->package->name ?? null,
                'submitted_at' => $app->submitted_at?->toIso8601String(),
            ]
        ]);
    }
}
