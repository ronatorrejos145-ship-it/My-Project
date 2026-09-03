<?php

namespace App\Http\Controllers;

use App\Models\InstallationWorkOrder;
use App\Services\InstallationAcceptanceService;
use App\Services\InstallationArrivalService;
use App\Services\InstallationChecklistService;
use App\Services\InstallationCompletionService;
use App\Services\InstallationCreationService;
use App\Services\InstallationDispatchService;
use App\Services\InstallationEquipmentService;
use App\Services\InstallationMaterialService;
use App\Services\InstallationTestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstallationApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = InstallationWorkOrder::with(['customer', 'package', 'assignedTechnician', 'serviceArea'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('technician_id')) {
            $query->where('assigned_technician_id', $request->technician_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(15),
        ]);
    }

    public function show(InstallationWorkOrder $installation): JsonResponse
    {
        $installation->load([
            'customer',
            'application',
            'technicalSurvey',
            'package',
            'assignedTechnician',
            'checklistResponses.item',
            'photos',
            'materials',
            'equipment',
            'tests',
            'acceptances',
            'handoff',
        ]);

        return response()->json([
            'success' => true,
            'data' => $installation,
        ]);
    }

    public function createFromSurvey(Request $request, InstallationCreationService $creationService): JsonResponse
    {
        $validated = $request->validate([
            'technical_survey_id' => 'required|exists:technical_surveys,id',
            'work_type' => 'nullable|string',
            'priority' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $workOrder = $creationService->createFromApprovedSurvey(
            $validated['technical_survey_id'],
            $validated,
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Installation work order created successfully.',
            'data' => $workOrder,
        ], 201);
    }

    public function arrive(Request $request, InstallationWorkOrder $installation, InstallationArrivalService $arrivalService): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'gps_accuracy' => 'nullable|numeric',
            'allow_override' => 'nullable|boolean',
            'override_reason' => 'nullable|string',
        ]);

        $workOrder = $arrivalService->recordArrival(
            $installation,
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $validated['gps_accuracy'] ?? null,
            $request->boolean('allow_override'),
            $validated['override_reason'] ?? null,
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Technician arrival recorded and verified via GPS.',
            'data' => $workOrder,
        ]);
    }

    public function complete(Request $request, InstallationWorkOrder $installation, InstallationCompletionService $completionService): JsonResponse
    {
        $workOrder = $completionService->completeInstallation(
            $installation,
            auth()->id(),
            $request->boolean('bypass_validation'),
            $request->input('bypass_reason')
        );

        return response()->json([
            'success' => true,
            'message' => 'Installation completed and handed off for activation.',
            'data' => $workOrder->load('handoff'),
        ]);
    }
}
