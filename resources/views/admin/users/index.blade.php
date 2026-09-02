@extends('layouts.app')

@section('title', 'Users')
@section('header', 'User Management')

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone..." class="px-3 py-1.5 border border-gray-300 rounded text-sm">
            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded text-sm">Filter</button>
        </form>

        @can('create', App\Models\User::class)
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded text-sm">
            Add User
        </a>
        @endcan
    </div>

    <div class="bg-white shadow-sm rounded border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Email</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Role(s)</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Last Login</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $u)
                <tr>
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $u->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $u->email }}</td>
                    <td class="px-4 py-3">
                        @foreach($u->roles as $role)
                            <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700 rounded">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ ($u->status instanceof App\Enums\UserStatus ? $u->status->value : $u->status) === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                            {{ $u->status instanceof App\Enums\UserStatus ? $u->status->value : $u->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        @can('update', $u)
                        <a href="{{ route('admin.users.edit', $u) }}" class="text-sky-600 hover:text-sky-900 text-xs font-semibold">Edit</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $users->links() }}
    </div>
</div>
@endsection
