@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Asset: {{ $asset->asset_tag }}</h1>
            <p class="text-sm text-gray-600">Category: {{ $asset->category->name ?? 'N/A' }} | Status: <span class="font-semibold text-blue-600">{{ $asset->current_status }}</span></p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('assets.qr.lookup', $asset->asset_tag) }}" target="_blank" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-xs font-bold">
                View QR Lookup Card
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-3 gap-6 mb-6">
        <!-- Overview Card -->
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Hardware Specifications</h3>
            <p class="text-xs mb-1"><strong>Manufacturer:</strong> {{ $asset->manufacturer ?? 'N/A' }}</p>
            <p class="text-xs mb-1"><strong>Serial Number:</strong> <span class="font-mono bg-gray-100 px-1">{{ $asset->serial_number ?? 'N/A' }}</span></p>
            <p class="text-xs mb-1"><strong>MAC Address:</strong> <span class="font-mono bg-gray-100 px-1">{{ $asset->mac_address ?? 'N/A' }}</span></p>
            <p class="text-xs mb-1"><strong>Condition:</strong> {{ $asset->condition }}</p>
            <p class="text-xs mb-1"><strong>Current Location:</strong> {{ $asset->current_location ?? 'Warehouse' }}</p>
        </div>

        <!-- Warranty Card -->
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Warranty & Purchase Details</h3>
            <p class="text-xs mb-1"><strong>Purchase Cost:</strong> PHP {{ number_format($asset->purchase_cost, 2) }}</p>
            <p class="text-xs mb-1"><strong>Warranty Expiry:</strong> {{ $asset->warranty_end ? $asset->warranty_end->format('Y-m-d') : 'N/A' }}</p>
            <p class="text-xs mt-2">
                <span class="px-2 py-1 text-xs font-bold rounded bg-{{ $warrantyInfo['badge'] }}-100 text-{{ $warrantyInfo['badge'] }}-800">
                    Warranty Status: {{ $warrantyInfo['status'] }} ({{ $warrantyInfo['days_remaining'] }} days remaining)
                </span>
            </p>
        </div>

        <!-- Assignee Card -->
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Active Assignment</h3>
            @if($asset->assignedCustomer)
                <p class="text-xs"><strong>Assigned Customer:</strong> {{ $asset->assignedCustomer->full_name ?? $asset->assignedCustomer->first_name . ' ' . $asset->assignedCustomer->last_name }} ({{ $asset->assignedCustomer->customer_number }})</p>
            @elseif($asset->assignedEmployee)
                <p class="text-xs"><strong>Assigned Employee:</strong> {{ $asset->assignedEmployee->first_name }} {{ $asset->assignedEmployee->last_name }} ({{ $asset->assignedEmployee->employee_number }})</p>
            @else
                <p class="text-xs text-gray-500">Unassigned (In Inventory)</p>
            @endif
        </div>
    </div>

    <!-- History Timelines -->
    <div class="bg-white p-5 rounded-lg shadow mb-6">
        <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Status & Movement History</h3>
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50 border-b"><th class="text-left p-2">Date</th><th class="text-left p-2">Action / Status</th><th class="text-left p-2">Location</th><th class="text-left p-2">Performed By</th></tr>
            </thead>
            <tbody>
                @forelse($asset->histories as $hist)
                    <tr class="border-b">
                        <td class="p-2">{{ $hist->created_at->format('Y-m-d H:i') }}</td>
                        <td class="p-2"><span class="font-bold">{{ $hist->action }}</span> ({{ $hist->new_status }})</td>
                        <td class="p-2">{{ $hist->new_location }}</td>
                        <td class="p-2">{{ $hist->user->name ?? 'System' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-2 text-gray-500 text-center">No history recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
