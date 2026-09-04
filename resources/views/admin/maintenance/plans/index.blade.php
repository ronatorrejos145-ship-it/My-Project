@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Preventive Maintenance Plans</h1>
            <p class="text-sm text-gray-600">Configure recurring inspection and maintenance routines.</p>
        </div>
        <form action="{{ route('admin.maintenance.plans.trigger') }}" method="POST">
            @csrf
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded font-medium text-sm hover:bg-indigo-700">Trigger Scheduled Jobs</button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">New Maintenance Plan</h2>
            <form action="{{ route('admin.maintenance.plans.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Plan Name *</label>
                    <input type="text" name="name" required class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Maintenance Type *</label>
                    <select name="maintenance_type" required class="w-full border rounded px-3 py-2 text-sm">
                        <option value="PREVENTIVE">PREVENTIVE</option>
                        <option value="INSPECTION">INSPECTION</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Frequency *</label>
                    <select name="frequency" required class="w-full border rounded px-3 py-2 text-sm">
                        <option value="DAILY">DAILY</option>
                        <option value="WEEKLY">WEEKLY</option>
                        <option value="MONTHLY" selected>MONTHLY</option>
                        <option value="QUARTERLY">QUARTERLY</option>
                        <option value="ANNUAL">ANNUAL</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Est. Duration (mins) *</label>
                    <input type="number" name="estimated_duration_minutes" value="60" required class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full border rounded px-3 py-2 text-sm"></textarea>
                </div>
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded text-sm font-medium hover:bg-green-700">Save Maintenance Plan</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-bold text-gray-800">Active Maintenance Plans</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Frequency</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($plans as $plan)
                        <tr>
                            <td class="px-6 py-4 font-bold text-indigo-600">{{ $plan->plan_code }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $plan->name }}</td>
                            <td class="px-6 py-4 text-xs font-bold uppercase">{{ $plan->frequency }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded font-bold bg-green-100 text-green-800">ACTIVE</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No active maintenance plans.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
