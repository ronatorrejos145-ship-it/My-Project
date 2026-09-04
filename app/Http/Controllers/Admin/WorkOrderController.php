<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\User;
use App\Services\WorkOrderService;
use App\Services\WorkOrderStateService;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function __construct(
        protected WorkOrderService $workOrderService,
        protected WorkOrderStateService $stateService
    ) {}

    public function index(Request $request)
    {
        $query = WorkOrder::with(['customer', 'assignedTechnician', 'ticket'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('work_order_type')) {
            $query->where('work_order_type', $request->work_order_type);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('work_order_number', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('first_name', 'like', "%{$s}%")->orWhere('last_name', 'like', "%{$s}%"));
            });
        }

        $workOrders = $query->paginate(15);
        $technicians = User::all();

        return view('admin.maintenance.work_orders.index', compact('workOrders', 'technicians'));
    }

    public function create()
    {
        $customers = Customer::limit(50)->get();
        $technicians = User::all();

        return view('admin.maintenance.work_orders.create', compact('customers', 'technicians'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'subscription_id' => 'nullable|exists:subscriptions,id',
            'work_order_type' => 'required|string',
            'priority' => 'required|string',
            'severity' => 'required|string',
            'description' => 'nullable|string',
            'service_address' => 'nullable|string',
            'assigned_technician_id' => 'nullable|exists:users,id',
            'scheduled_start_at' => 'nullable|date',
            'scheduled_end_at' => 'nullable|date',
        ]);

        $workOrder = $this->workOrderService->createWorkOrder($validated, auth()->id());

        return redirect()->route('admin.maintenance.work-orders.show', $workOrder->id)
            ->with('success', 'Work Order ' . $workOrder->work_order_number . ' created successfully.');
    }

    public function show($id)
    {
        $workOrder = WorkOrder::with([
            'customer',
            'subscription',
            'assignedTechnician',
            'statusHistories.changedBy',
            'assignments.technician',
            'gpsEvents',
            'checklistResults.item',
            'diagnostics.technician',
            'photos',
            'materials.item',
            'tools.tool',
            'equipmentReplacements.oldAsset',
            'equipmentReplacements.newAsset',
            'customerConfirmation',
            'failures',
            'downtime'
        ])->findOrFail($id);

        $technicians = User::all();

        return view('admin.maintenance.work_orders.show', compact('workOrder', 'technicians'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $this->stateService->transition($workOrder, $request->status, auth()->id(), $request->reason, $request->notes, $request->ip());

        return redirect()->back()->with('success', 'Work order status updated to ' . $request->status);
    }
}
