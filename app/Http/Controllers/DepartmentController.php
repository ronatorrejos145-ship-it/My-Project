<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Department::class);

        $departments = Department::withCount('employees')->get();

        return view('admin.departments.index', compact('departments'));
    }

    public function create(): View
    {
        $this->authorize('create', Department::class);

        return view('admin.departments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Department::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:departments', 'alpha_dash'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        $department = Department::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        AuditLogService::log('department.created', 'departments', $department, null, $department->toArray());

        return redirect()->route('admin.departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department): View
    {
        $this->authorize('update', $department);

        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        $oldValues = $department->toArray();

        $department->update($validated);

        AuditLogService::log('department.updated', 'departments', $department, $oldValues, $department->toArray());

        return redirect()->route('admin.departments.index')->with('success', 'Department updated successfully.');
    }
}
