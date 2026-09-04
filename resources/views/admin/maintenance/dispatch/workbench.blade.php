@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dispatch Workbench</h1>
            <p class="text-sm text-gray-600">Assign, schedule, and optimize field technician work orders.</p>
        </div>
        <div>
            <a href="{{ route('admin.maintenance.work-orders.index') }}" class="px-4 py-2 border rounded text-sm font-medium">Work Orders List</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Unassigned Queue -->
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-base font-bold text-gray-800 mb-3 flex justify-between items-center border-b pb-2">
                <span>Unassigned Queue</span>
                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full font-bold">{{ count($unassignedWorkOrders) }}</span>
            </h2>

            <div class="space-y-3 max-h-screen overflow-y-auto">
                @forelse($unassignedWorkOrders as $wo)
                    <div class="p-3 border rounded-lg bg-gray-50 hover:bg-white hover:shadow transition">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-bold text-xs text-indigo-600">{{ $wo->work_order_number }}</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-red-100 text-red-800">{{ $wo->priority }}</span>
                        </div>
                        <h3 class="font-semibold text-sm text-gray-900">{{ $wo->title }}</h3>
                        <p class="text-xs text-gray-500 mb-2">{{ $wo->customer ? $wo->customer->first_name . ' ' . $wo->customer->last_name : 'No Customer' }}</p>

                        <form action="{{ route('admin.maintenance.dispatch.assign', $wo->id) }}" method="POST" class="mt-2 flex space-x-2">
                            @csrf
                            <select name="technician_id" required class="border rounded text-xs px-2 py-1 flex-1">
                                <option value="">Select Tech...</option>
                                @foreach($technicians as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->active_jobs }} active)</option>
                                @endforeach
                            </select>
                            <button type="submit" class="bg-indigo-600 text-white text-xs px-3 py-1 rounded font-medium hover:bg-indigo-700">Assign</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">No unassigned work orders.</p>
                @endforelse
            </div>
        </div>

        <!-- Assigned / Active Queue -->
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-base font-bold text-gray-800 mb-3 flex justify-between items-center border-b pb-2">
                <span>Active Field Jobs</span>
                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-bold">{{ count($assignedWorkOrders) }}</span>
            </h2>

            <div class="space-y-3 max-h-screen overflow-y-auto">
                @forelse($assignedWorkOrders as $wo)
                    <div class="p-3 border rounded-lg bg-white shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-bold text-xs text-indigo-600">{{ $wo->work_order_number }}</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-yellow-100 text-yellow-800">{{ $wo->status }}</span>
                        </div>
                        <h3 class="font-semibold text-sm text-gray-900">{{ $wo->title }}</h3>
                        <p class="text-xs text-gray-600 font-medium mt-1">Tech: {{ $wo->assignedTechnician ? $wo->assignedTechnician->name : 'Unassigned' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">No active field jobs.</p>
                @endforelse
            </div>
        </div>

        <!-- Technician Availability Roster -->
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-base font-bold text-gray-800 mb-3 border-b pb-2">Technician Workload Roster</h2>
            <div class="space-y-3">
                @foreach($technicians as $tech)
                    <div class="flex justify-between items-center p-3 border rounded bg-gray-50">
                        <div>
                            <div class="font-semibold text-sm text-gray-900">{{ $tech->name }}</div>
                            <div class="text-xs text-gray-500">{{ $tech->email }}</div>
                        </div>
                        <span class="px-2 py-1 rounded text-xs font-bold bg-indigo-100 text-indigo-800">
                            {{ $tech->active_jobs }} Active Job(s)
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
