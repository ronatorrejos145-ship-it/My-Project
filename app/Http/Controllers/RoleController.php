<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::withCount('users', 'permissions')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        $permissions = Permission::all()->groupBy('module');

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:roles', 'alpha_dash'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'],
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        AuditLogService::log('role.created', 'settings', $role, null, $role->toArray());

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        $role->load('permissions');
        $permissions = Permission::all()->groupBy('module');

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $oldValues = $role->toArray();

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        AuditLogService::log('role.updated', 'settings', $role, $oldValues, $role->toArray());

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }
}
