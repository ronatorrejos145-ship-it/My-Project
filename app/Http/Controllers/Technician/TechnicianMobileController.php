<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\WorkOrderChecklistTemplate;
use App\Models\Item;
use App\Models\Tool;
use App\Models\Asset;
use App\Services\WorkOrderGpsService;
use App\Services\WorkOrderChecklistService;
use App\Services\WorkOrderDiagnosticService;
use App\Services\WorkOrderMaterialService;
use App\Services\WorkOrderToolService;
use App\Services\EquipmentReplacementService;
use App\Services\WorkOrderCompletionService;
use Illuminate\Http\Request;

class TechnicianMobileController extends Controller
{
    public function __construct(
        protected WorkOrderGpsService $gpsService,
        protected WorkOrderChecklistService $checklistService,
        protected WorkOrderDiagnosticService $diagnosticService,
        protected WorkOrderMaterialService $materialService,
        protected WorkOrderToolService $toolService,
        protected EquipmentReplacementService $replacementService,
        protected WorkOrderCompletionService $completionService
    ) {}

    public function dashboard()
    {
        $userId = auth()->id();

        $assignedJobs = WorkOrder::with('customer')
            ->where('assigned_technician_id', $userId)
            ->whereNotIn('status', ['CLOSED', 'CANCELLED'])
            ->orderByRaw("FIELD(status, 'ON_SITE', 'IN_PROGRESS', 'EN_ROUTE', 'ASSIGNED', 'SCHEDULED')")
            ->get();

        return view('technician.dashboard', compact('assignedJobs'));
    }

    public function show($id)
    {
        $workOrder = WorkOrder::with([
            'customer',
            'subscription',
            'diagnostics',
            'materials.item',
            'tools.tool',
            'checklistResults',
            'photos',
            'equipmentReplacements'
        ])->findOrFail($id);

        $items = Item::limit(50)->get();
        $tools = Tool::limit(50)->get();
        $assets = Asset::limit(50)->get();
        $checklistTemplates = WorkOrderChecklistTemplate::with('items')->get();

        return view('technician.work_orders.show', compact('workOrder', 'items', 'tools', 'assets', 'checklistTemplates'));
    }

    public function recordGps(Request $request, $id)
    {
        $request->validate([
            'event_type' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric',
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $this->gpsService->recordGpsEvent(
            $workOrder,
            auth()->id(),
            $request->event_type,
            (float) $request->latitude,
            (float) $request->longitude,
            $request->accuracy ? (float) $request->accuracy : null,
            $request->userAgent()
        );

        return redirect()->back()->with('success', 'GPS Event Recorded: ' . $request->event_type);
    }

    public function recordDiagnostic(Request $request, $id)
    {
        $validated = $request->validate([
            'wan_status' => 'nullable|string',
            'lan_status' => 'nullable|string',
            'wifi_status' => 'nullable|string',
            'rx_power_dbm' => 'nullable|numeric',
            'download_speed_mbps' => 'nullable|numeric',
            'upload_speed_mbps' => 'nullable|numeric',
            'latency_ms' => 'nullable|numeric',
            'diagnosis_notes' => 'nullable|string',
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $this->diagnosticService->recordDiagnostic($workOrder, $validated, auth()->id());

        return redirect()->back()->with('success', 'Technical diagnostics saved.');
    }

    public function consumeMaterial(Request $request, $id)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'serial_number' => 'nullable|string',
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $this->materialService->consumeMaterial(
            $workOrder,
            $request->item_id,
            (float) $request->quantity,
            null,
            $request->serial_number,
            auth()->id()
        );

        return redirect()->back()->with('success', 'Material consumed successfully.');
    }

    public function replaceEquipment(Request $request, $id)
    {
        $request->validate([
            'old_serial' => 'nullable|string',
            'old_mac' => 'nullable|string',
            'new_asset_id' => 'nullable|exists:assets,id',
            'new_serial' => 'nullable|string',
            'new_mac' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $this->replacementService->replaceEquipment(
            $workOrder,
            null,
            $request->old_serial,
            $request->old_mac,
            $request->new_asset_id,
            $request->new_serial,
            $request->new_mac,
            $request->reason,
            auth()->id()
        );

        return redirect()->back()->with('success', 'Equipment replacement logged.');
    }

    public function completeJob(Request $request, $id)
    {
        $validated = $request->validate([
            'actual_root_cause' => 'nullable|string',
            'corrective_action' => 'nullable|string',
            'preventive_action' => 'nullable|string',
            'confirmed_by_name' => 'nullable|string',
            'signature_file_path' => 'nullable|string',
            'rating' => 'nullable|integer|between:1,5',
            'customer_comments' => 'nullable|string',
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $this->completionService->completeWorkOrder($workOrder, $validated, auth()->id());

        return redirect()->route('technician.dashboard')->with('success', 'Work order ' . $workOrder->work_order_number . ' completed successfully.');
    }
}
