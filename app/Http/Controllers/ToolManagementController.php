<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Tool;
use App\Models\ToolCheckout;
use App\Services\ToolCheckoutService;
use App\Services\ToolInspectionService;
use Illuminate\Http\Request;

class ToolManagementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Tool::class);

        $tools = Tool::with(['category', 'assignedEmployee'])->latest()->paginate(15);
        $employees = Employee::where('employment_status', 'ACTIVE')->get();

        return view('admin.tools.index', compact('tools', 'employees'));
    }

    public function checkout(Request $request, Tool $tool, ToolCheckoutService $checkoutService)
    {
        $this->authorize('update', $tool);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'expected_return_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $checkout = $checkoutService->checkoutTool(
            $tool,
            $employee,
            $validated['expected_return_date'] ?? null,
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('success', "Tool {$tool->tool_code} checked out to {$employee->first_name} {$employee->last_name}.");
    }

    public function return(Request $request, ToolCheckout $checkout, ToolCheckoutService $checkoutService)
    {
        $this->authorize('update', Tool::class);

        $validated = $request->validate([
            'condition' => 'required|string|in:NEW,GOOD,FAIR,POOR,DAMAGED',
            'notes' => 'nullable|string',
        ]);

        $checkoutService->returnTool(
            $checkout,
            $validated['condition'],
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('success', "Tool returned and updated in inventory.");
    }
}
