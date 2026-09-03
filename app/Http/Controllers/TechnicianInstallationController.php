<?php

namespace App\Http\Controllers;

use App\Models\InstallationWorkOrder;
use App\Services\InstallationAcceptanceService;
use App\Services\InstallationArrivalService;
use App\Services\InstallationChecklistService;
use App\Services\InstallationDispatchService;
use App\Services\InstallationEquipmentService;
use App\Services\InstallationMaterialService;
use App\Services\InstallationTestService;
use Illuminate\Http\Request;

class TechnicianInstallationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee ?? null;

        $query = InstallationWorkOrder::with(['customer', 'package', 'serviceArea'])
            ->latest();

        if ($employee) {
            $query->where('assigned_technician_id', $employee->id);
        }

        $jobs = $query->get();

        return view('technician.installations.index', compact('jobs'));
    }

    public function show(InstallationWorkOrder $installation, InstallationChecklistService $checklistService)
    {
        $installation->load([
            'customer',
            'technicalSurvey',
            'package',
            'checklistResponses.item',
            'photos',
            'materials',
            'equipment',
            'tests',
        ]);

        $checklistTemplate = $checklistService->getDefaultTemplate($installation->work_type);

        return view('technician.installations.show', compact('installation', 'checklistTemplate'));
    }

    public function dispatchEnRoute(InstallationWorkOrder $installation, InstallationDispatchService $dispatchService)
    {
        $dispatchService->dispatchEnRoute($installation, auth()->id());

        return back()->with('success', 'Status updated: En Route to site.');
    }

    public function arrive(Request $request, InstallationWorkOrder $installation, InstallationArrivalService $arrivalService)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'gps_accuracy' => 'nullable|numeric',
            'allow_override' => 'nullable|boolean',
            'override_reason' => 'nullable|string',
        ]);

        $arrivalService->recordArrival(
            $installation,
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $validated['gps_accuracy'] ?? null,
            $request->boolean('allow_override'),
            $validated['override_reason'] ?? null,
            auth()->id()
        );

        return back()->with('success', 'Arrival verified on site via GPS.');
    }

    public function saveChecklist(Request $request, InstallationWorkOrder $installation, InstallationChecklistService $checklistService)
    {
        $validated = $request->validate([
            'checklist_item_id' => 'required|exists:installation_checklist_items,id',
            'value' => 'required',
            'notes' => 'nullable|string',
        ]);

        $checklistService->recordResponse(
            $installation,
            $validated['checklist_item_id'],
            $validated['value'],
            null,
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('success', 'Checklist item recorded.');
    }

    public function issueMaterial(Request $request, InstallationWorkOrder $installation, InstallationMaterialService $materialService)
    {
        $validated = $request->validate([
            'item_id' => 'nullable|exists:items,id',
            'item_name' => 'required|string',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $materialService->issueMaterial(
            $installation,
            $validated['item_id'] ?? null,
            $validated['item_name'],
            (float) $validated['quantity'],
            $validated['unit'],
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('success', 'Material logged for work order.');
    }

    public function assignEquipment(Request $request, InstallationWorkOrder $installation, InstallationEquipmentService $equipmentService)
    {
        $validated = $request->validate([
            'asset_id' => 'nullable|exists:assets,id',
            'equipment_type' => 'required|string',
            'model_name' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'mac_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $equipmentService->assignEquipment(
            $installation,
            $validated['equipment_type'],
            $validated['asset_id'] ?? null,
            $validated['model_name'] ?? null,
            $validated['serial_number'] ?? null,
            $validated['mac_address'] ?? null,
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('success', 'Equipment asset assigned to work order.');
    }

    public function recordTest(Request $request, InstallationWorkOrder $installation, InstallationTestService $testService)
    {
        $validated = $request->validate([
            'test_type' => 'required|string',
            'measured_value' => 'required|string',
            'unit' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $testService->recordTest(
            $installation,
            $validated['test_type'],
            $validated['measured_value'],
            $validated['unit'] ?? null,
            null,
            'Technician Field Device',
            null,
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('success', 'Technical test recorded.');
    }
}
