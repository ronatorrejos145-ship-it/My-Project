@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Maintenance Analytics & Reports</h1>
        <p class="text-sm text-gray-600">First-time fix rates, SLA compliance, downtime, and material expenditure metrics.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-indigo-600">
            <span class="text-xs font-semibold uppercase text-gray-500">Total Work Orders</span>
            <div class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($metrics['total_work_orders']) }}</div>
        </div>

        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-600">
            <span class="text-xs font-semibold uppercase text-gray-500">First-Time Fix Rate</span>
            <div class="text-3xl font-bold text-green-600 mt-1">{{ $metrics['first_time_fix_rate'] }}%</div>
        </div>

        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-600">
            <span class="text-xs font-semibold uppercase text-gray-500">SLA Compliance</span>
            <div class="text-3xl font-bold text-blue-600 mt-1">{{ $metrics['sla_compliance_rate'] }}%</div>
        </div>

        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-600">
            <span class="text-xs font-semibold uppercase text-gray-500">Total Downtime (Mins)</span>
            <div class="text-3xl font-bold text-red-600 mt-1">{{ number_format($metrics['total_downtime_minutes']) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-base font-bold text-gray-800 mb-3 border-b pb-2">Operational Summary</h2>
            <ul class="divide-y text-sm">
                <li class="py-2 flex justify-between">
                    <span class="text-gray-600">Completed Jobs:</span>
                    <span class="font-bold text-gray-800">{{ $metrics['completed'] }}</span>
                </li>
                <li class="py-2 flex justify-between">
                    <span class="text-gray-600">Open / In-Progress Jobs:</span>
                    <span class="font-bold text-gray-800">{{ $metrics['open'] }}</span>
                </li>
                <li class="py-2 flex justify-between">
                    <span class="text-gray-600">Revisit Jobs Required:</span>
                    <span class="font-bold text-red-600">{{ $metrics['revisit_count'] }}</span>
                </li>
                <li class="py-2 flex justify-between">
                    <span class="text-gray-600">Total Material Expenditure:</span>
                    <span class="font-bold text-gray-900">₱{{ number_format($metrics['total_material_cost'], 2) }}</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
