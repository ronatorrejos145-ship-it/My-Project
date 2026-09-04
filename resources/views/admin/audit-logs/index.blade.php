@extends('layouts.app')

@section('title', 'Audit Logs')
@section('header', 'System Audit Trail')

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search action, IP, user..." class="px-3 py-1.5 border border-gray-300 rounded text-sm">
            <select name="module" class="px-3 py-1.5 border border-gray-300 rounded text-sm">
                <option value="">All Modules</option>
                <option value="auth" {{ request('module') === 'auth' ? 'selected' : '' }}>Auth</option>
                <option value="users" {{ request('module') === 'users' ? 'selected' : '' }}>Users</option>
                <option value="settings" {{ request('module') === 'settings' ? 'selected' : '' }}>Settings</option>
                <option value="employees" {{ request('module') === 'employees' ? 'selected' : '' }}>Employees</option>
                <option value="customers" {{ request('module') === 'customers' ? 'selected' : '' }}>Customers</option>
            </select>
            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded text-sm">Filter</button>
        </form>
    </div>

    <div class="bg-white shadow-sm rounded border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Timestamp</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">User</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Action</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Module</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 font-mono text-xs">
                @forelse($auditLogs as $log)
                <tr>
                    <td class="px-4 py-3 text-gray-500">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    <td class="px-4 py-3 text-gray-900 font-sans font-medium">{{ $log->user ? $log->user->name : 'System/Guest' }}</td>
                    <td class="px-4 py-3 text-sky-700 font-semibold">{{ $log->action }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 bg-slate-100 rounded text-slate-700">{{ $log->module }}</span></td>
                    <td class="px-4 py-3 text-gray-600">{{ $log->ip_address ?: 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-500 font-sans">No audit events logged yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $auditLogs->links() }}
    </div>
</div>
@endsection
