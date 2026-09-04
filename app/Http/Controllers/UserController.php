<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);
        $roles = Role::all();
        $statuses = UserStatus::cases();

        return view('admin.users.create', compact('roles', 'statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', Rules\Password::defaults()],
            'status' => ['required', 'string'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
        ]);

        if (!empty($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        AuditLogService::log('user.created', 'users', $user, null, $user->toArray());

        return redirect()->route('admin.users.index')->with('success', 'User account created successfully.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);
        $user->load('roles');
        $roles = Role::all();
        $statuses = UserStatus::cases();

        return view('admin.users.edit', compact('user', 'roles', 'statuses'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', Rules\Password::defaults()],
            'status' => ['required', 'string'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $oldValues = $user->toArray();

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->status = $validated['status'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        AuditLogService::log('user.updated', 'users', $user, $oldValues, $user->toArray());

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $oldValues = $user->toArray();
        $user->delete();

        AuditLogService::log('user.deleted', 'users', null, $oldValues, null);

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
