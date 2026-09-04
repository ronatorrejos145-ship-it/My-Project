@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'System Overview')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">Welcome, {{ $user->name }}!</h2>
        <p class="text-sm text-gray-600 mt-1">Role: <span class="font-semibold text-sky-600">{{ $user->roles->pluck('name')->implode(', ') ?: 'Standard Account' }}</span></p>
    </div>

    <!-- Foundation Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Users</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $metrics['total_users'] }}</p>
            <p class="text-xs text-emerald-600 mt-1">{{ $metrics['active_users'] }} Active</p>
        </div>

        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Employees</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $metrics['total_employees'] }}</p>
        </div>

        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Customers</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $metrics['total_customers'] }}</p>
        </div>

        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Departments</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $metrics['total_departments'] }}</p>
        </div>

        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">System Status</p>
            <span class="inline-block mt-2 px-2.5 py-1 text-xs font-semibold text-emerald-800 bg-emerald-100 rounded-full">
                Operational
            </span>
        </div>
    </div>

    <!-- Phase Status Info -->
    <div class="bg-slate-900 text-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-bold text-sky-400">Phase 1 Foundation Active</h3>
        <p class="text-sm text-slate-300 mt-1">
            Secure Authentication, RBAC Policies, Department System, Employee Foundation, Customer Foundation, Audit Logging, and System Settings are operational.
        </p>
    </div>
</div>
@endsection
