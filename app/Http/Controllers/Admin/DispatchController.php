<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\User;
use App\Services\DispatchService;
use Illuminate\Http\Request;

class DispatchController extends Controller
{
    public function __construct(
        protected DispatchService $dispatchService
    ) {}

    public function workbench()
    {
        $unassignedWorkOrders = WorkOrder::with('customer')
            ->whereIn('status', ['PENDING', 'APPROVED', 'DRAFT'])
            ->orderByRaw("FIELD(priority, 'CRITICAL', 'URGENT', 'HIGH', 'NORMAL', 'LOW')")
            ->latest()
            ->get();

        $assignedWorkOrders = WorkOrder::with(['customer', 'assignedTechnician'])
            ->whereIn('status', ['ASSIGNED', 'SCHEDULED', 'EN_ROUTE', 'ON_SITE', 'IN_PROGRESS', 'WAITING_MATERIALS', 'TESTING'])
            ->latest()
            ->get();

        $technicians = User::withCount(['assignedWorkOrders as active_jobs' => function ($q) {
            $q->whereIn('status', ['ASSIGNED', 'SCHEDULED', 'EN_ROUTE', 'ON_SITE', 'IN_PROGRESS']);
        }])->get();

        return view('admin.maintenance.dispatch.workbench', compact('unassignedWorkOrders', 'assignedWorkOrders', 'technicians'));
    }

    public function assign(Request $request, $id)
    {
        $request->validate([
            'technician_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $this->dispatchService->assignTechnician($workOrder, $request->technician_id, auth()->id(), null, $request->notes);

        return redirect()->back()->with('success', 'Technician assigned successfully.');
    }

    public function schedule(Request $request, $id)
    {
        $request->validate([
            'scheduled_start_at' => 'required|date',
            'scheduled_end_at' => 'required|date|after_or_equal:scheduled_start_at',
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $this->dispatchService->scheduleWorkOrder($workOrder, $request->scheduled_start_at, $request->scheduled_end_at, auth()->id());

        return redirect()->back()->with('success', 'Work order scheduled successfully.');
    }
}
