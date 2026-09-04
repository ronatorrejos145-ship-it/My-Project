<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use App\Services\WorkOrderGpsService;
use App\Services\WorkOrderCompletionService;
use Illuminate\Http\Request;

class WorkOrderApiController extends Controller
{
    public function __construct(
        protected WorkOrderService $workOrderService,
        protected WorkOrderGpsService $gpsService,
        protected WorkOrderCompletionService $completionService
    ) {}

    public function index(Request $request)
    {
        $userId = auth()->id();

        $workOrders = WorkOrder::with(['customer', 'assignedTechnician'])
            ->when($request->filled('assigned_to_me'), fn($q) => $q->where('assigned_technician_id', $userId))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $workOrders
        ]);
    }

    public function show($id)
    {
        $workOrder = WorkOrder::with([
            'customer',
            'subscription',
            'assignedTechnician',
            'diagnostics',
            'materials.item',
            'photos',
            'customerConfirmation'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $workOrder
        ]);
    }

    public function recordGps(Request $request, $id)
    {
        $validated = $request->validate([
            'event_type' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric',
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $event = $this->gpsService->recordGpsEvent(
            $workOrder,
            auth()->id(),
            $validated['event_type'],
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $validated['accuracy'] ?? null,
            $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => 'GPS event recorded.',
            'data' => $event
        ]);
    }
}
