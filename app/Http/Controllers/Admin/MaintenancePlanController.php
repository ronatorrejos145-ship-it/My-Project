<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenancePlan;
use App\Models\MaintenancePlanSchedule;
use App\Services\PreventiveMaintenanceService;
use Illuminate\Http\Request;

class MaintenancePlanController extends Controller
{
    public function __construct(
        protected PreventiveMaintenanceService $preventiveService
    ) {}

    public function index()
    {
        $plans = MaintenancePlan::withCount('schedules')->latest()->get();
        $schedules = MaintenancePlanSchedule::with(['plan', 'customer', 'asset'])->latest()->paginate(15);

        return view('admin.maintenance.plans.index', compact('plans', 'schedules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'maintenance_type' => 'required|string',
            'frequency' => 'required|string',
            'estimated_duration_minutes' => 'required|integer|min:15',
            'description' => 'nullable|string',
        ]);

        $validated['plan_code'] = 'PLAN-' . strtoupper(\Illuminate\Support\Str::random(6));
        $validated['created_by_user_id'] = auth()->id();

        MaintenancePlan::create($validated);

        return redirect()->back()->with('success', 'Preventive Maintenance Plan created.');
    }

    public function triggerSchedules()
    {
        $count = $this->preventiveService->generateScheduledWorkOrders();

        return redirect()->back()->with('success', "Generated {$count} preventive maintenance work order(s).");
    }
}
