<?php

namespace App\Http\Controllers;

use App\Enums\EmployeeStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        $query = Employee::with(['user', 'department']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('employee_number', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $employees = $query->latest()->paginate(15)->withQueryString();

        return view('admin.employees.index', compact('employees'));
    }

    public function create(): View
    {
        $this->authorize('create', Employee::class);

        $departments = Department::where('status', 'ACTIVE')->get();
        $users = User::whereDoesntHave('employee')->get();
        $statuses = EmployeeStatus::cases();

        return view('admin.employees.create', compact('departments', 'users', 'statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        $validated = $request->validate([
            'employee_number' => ['required', 'string', 'max:50', 'unique:employees'],
            'user_id' => ['required', 'exists:users,id', 'unique:employees,user_id'],
            'department_id' => ['required', 'exists:departments,id'],
            'position' => ['required', 'string', 'max:255'],
            'employment_status' => ['required', 'string'],
            'hire_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        $employee = Employee::create($validated);

        AuditLogService::log('employee.created', 'employees', $employee, null, $employee->toArray());

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);

        $employee->load(['user', 'department']);
        $departments = Department::where('status', 'ACTIVE')->get();
        $statuses = EmployeeStatus::cases();

        return view('admin.employees.edit', compact('employee', 'departments', 'statuses'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'position' => ['required', 'string', 'max:255'],
            'employment_status' => ['required', 'string'],
            'hire_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldValues = $employee->toArray();

        $employee->update($validated);

        AuditLogService::log('employee.updated', 'employees', $employee, $oldValues, $employee->toArray());

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }
}
