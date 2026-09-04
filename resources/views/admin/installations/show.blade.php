@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Work Order: {{ $installation->work_order_number }}</h1>
            <p class="text-sm text-gray-600">Status: <span class="font-semibold text-blue-600">{{ $installation->status }}</span> | Work Type: {{ $installation->work_type }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.installations.download-pdf', $installation) }}" target="_blank" class="px-3 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-sm font-medium">
                Download PDF
            </a>
            @if($installation->status !== 'COMPLETED')
                <form action="{{ route('admin.installations.complete', $installation) }}" method="POST" onsubmit="return confirm('Complete work order and generate activation handoff?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm font-medium">
                        Complete & Handoff
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-3 gap-6 mb-6">
        <!-- Overview Card -->
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Customer Details</h3>
            <p><strong>Name:</strong> {{ $installation->customer->full_name ?? $installation->customer->first_name . ' ' . $installation->customer->last_name }}</p>
            <p><strong>Package:</strong> {{ $installation->package->name ?? 'N/A' }} ({{ $installation->packageVersion->download_speed_mbps ?? 0 }} Mbps)</p>
            <p><strong>Address:</strong> {{ $installation->address->full_address ?? 'Captured Location' }}</p>
            <p><strong>GPS:</strong> {{ $installation->latitude }}, {{ $installation->longitude }}</p>
        </div>

        <!-- Assignment Card -->
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Technician Assignment</h3>
            <p><strong>Assigned Tech:</strong> {{ $installation->assignedTechnician ? ($installation->assignedTechnician->first_name . ' ' . $installation->assignedTechnician->last_name) : 'Unassigned' }}</p>
            <p><strong>Supervisor:</strong> {{ $installation->supervisor ? ($installation->supervisor->first_name . ' ' . $installation->supervisor->last_name) : 'N/A' }}</p>
            <form action="{{ route('admin.installations.assign', $installation) }}" method="POST" class="mt-3">
                @csrf
                <select name="technician_id" class="w-full text-xs border-gray-300 rounded mb-2">
                    <option value="">-- Reassign Tech --</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}">{{ $tech->first_name }} {{ $tech->last_name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full py-1 bg-blue-600 text-white rounded text-xs font-semibold">Assign Technician</button>
            </form>
        </div>

        <!-- Scheduling Card -->
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Schedule Appointment</h3>
            <p><strong>Scheduled:</strong> {{ $installation->scheduled_start ? $installation->scheduled_start->format('Y-m-d H:i') : 'Unscheduled' }}</p>
            <form action="{{ route('admin.installations.schedule', $installation) }}" method="POST" class="mt-3 text-xs">
                @csrf
                <input type="date" name="scheduled_date" value="{{ date('Y-m-d') }}" class="w-full border-gray-300 rounded mb-2 text-xs">
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <input type="time" name="start_time" value="09:00" class="border-gray-300 rounded text-xs">
                    <input type="time" name="end_time" value="11:00" class="border-gray-300 rounded text-xs">
                </div>
                <button type="submit" class="w-full py-1 bg-indigo-600 text-white rounded font-semibold">Set Schedule</button>
            </form>
        </div>
    </div>

    <!-- Equipment & Tests Summary -->
    <div class="grid grid-cols-2 gap-6 mb-6">
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Assigned Equipment</h3>
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b"><th class="text-left p-1">Type</th><th class="text-left p-1">Serial</th><th class="text-left p-1">MAC</th></tr>
                </thead>
                <tbody>
                    @forelse($installation->equipment as $eq)
                        <tr class="border-b"><td class="p-1">{{ $eq->equipment_type }}</td><td class="p-1">{{ $eq->serial_number }}</td><td class="p-1">{{ $eq->mac_address }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="p-2 text-gray-500">No equipment logged.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Technical Tests</h3>
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b"><th class="text-left p-1">Test</th><th class="text-left p-1">Value</th><th class="text-left p-1">Result</th></tr>
                </thead>
                <tbody>
                    @forelse($installation->tests as $test)
                        <tr class="border-b"><td class="p-1">{{ $test->test_type }}</td><td class="p-1">{{ $test->measured_value }} {{ $test->unit }}</td><td class="p-1"><span class="px-1 bg-green-100 text-green-800 font-bold rounded">{{ $test->result }}</span></td></tr>
                    @empty
                        <tr><td colspan="3" class="p-2 text-gray-500">No technical tests recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Handoff Status -->
    @if($installation->handoff)
        <div class="bg-green-50 border border-green-300 p-4 rounded-lg">
            <h3 class="font-bold text-green-800">Activation Handoff Status: {{ $installation->handoff->status }}</h3>
            <p class="text-xs text-green-700">Handed off on {{ $installation->handoff->handoff_at ? $installation->handoff->handoff_at->format('Y-m-d H:i:s') : 'N/A' }}. Ready for future Phase 11 Subscription Activation.</p>
        </div>
    @endif
</div>
@endsection
