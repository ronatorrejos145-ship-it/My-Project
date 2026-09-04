@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <div class="mb-4 flex justify-between items-center">
        <div>
            <span class="text-xs font-bold uppercase text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ $workOrder->status }}</span>
            <h1 class="text-xl font-bold text-gray-800">{{ $workOrder->work_order_number }}</h1>
            <p class="text-xs text-gray-600">{{ $workOrder->title }}</p>
        </div>
        <a href="{{ route('technician.dashboard') }}" class="text-xs bg-gray-200 px-3 py-2 rounded font-medium text-gray-700">Back</a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-xs">
            {{ session('success') }}
        </div>
    @endif

    <!-- GPS Workflow Controls -->
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <h2 class="font-bold text-sm text-gray-800 mb-2 border-b pb-1">1. GPS Operations</h2>
        <div class="grid grid-cols-2 gap-2">
            <form action="{{ route('technician.work-orders.record-gps', $workOrder->id) }}" method="POST">
                @csrf
                <input type="hidden" name="event_type" value="TRAVEL_STARTED">
                <input type="hidden" name="latitude" value="{{ $workOrder->latitude ?? 14.5995 }}">
                <input type="hidden" name="longitude" value="{{ $workOrder->longitude ?? 120.9842 }}">
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded text-xs font-bold hover:bg-blue-700">Start Travel</button>
            </form>

            <form action="{{ route('technician.work-orders.record-gps', $workOrder->id) }}" method="POST">
                @csrf
                <input type="hidden" name="event_type" value="ARRIVED">
                <input type="hidden" name="latitude" value="{{ $workOrder->latitude ?? 14.5995 }}">
                <input type="hidden" name="longitude" value="{{ $workOrder->longitude ?? 120.9842 }}">
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded text-xs font-bold hover:bg-green-700">Arrived On Site</button>
            </form>
        </div>
    </div>

    <!-- Technical Diagnostics -->
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <h2 class="font-bold text-sm text-gray-800 mb-2 border-b pb-1">2. Technical Diagnostics</h2>
        <form action="{{ route('technician.work-orders.record-diagnostic', $workOrder->id) }}" method="POST">
            @csrf
            <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                <div>
                    <label class="block text-gray-700 font-semibold">RX Power (dBm)</label>
                    <input type="number" step="0.01" name="rx_power_dbm" placeholder="-19.5" class="w-full border rounded p-1">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold">Download (Mbps)</label>
                    <input type="number" step="0.01" name="download_speed_mbps" placeholder="95.5" class="w-full border rounded p-1">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold">Upload (Mbps)</label>
                    <input type="number" step="0.01" name="upload_speed_mbps" placeholder="95.5" class="w-full border rounded p-1">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold">Latency (ms)</label>
                    <input type="number" step="0.01" name="latency_ms" placeholder="12" class="w-full border rounded p-1">
                </div>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-1.5 rounded text-xs font-bold">Save Diagnostics</button>
        </form>
    </div>

    <!-- Consume Materials & Replace Equipment -->
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <h2 class="font-bold text-sm text-gray-800 mb-2 border-b pb-1">3. Materials & Equipment Swaps</h2>
        <form action="{{ route('technician.work-orders.consume-material', $workOrder->id) }}" method="POST" class="mb-3">
            @csrf
            <div class="flex space-x-2 text-xs">
                <select name="item_id" required class="border rounded p-1 flex-1">
                    <option value="">Select Item...</option>
                    @foreach($items as $i)
                        <option value="{{ $i->id }}">{{ $i->name }}</option>
                    @endforeach
                </select>
                <input type="number" step="0.01" name="quantity" value="1" class="border rounded p-1 w-16">
                <button type="submit" class="bg-gray-800 text-white px-3 py-1 rounded font-bold">Use</button>
            </div>
        </form>

        <form action="{{ route('technician.work-orders.replace-equipment', $workOrder->id) }}" method="POST">
            @csrf
            <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                <input type="text" name="old_serial" placeholder="Old Serial #" class="border rounded p-1">
                <input type="text" name="new_serial" placeholder="New Serial #" class="border rounded p-1">
            </div>
            <button type="submit" class="w-full bg-purple-600 text-white py-1.5 rounded text-xs font-bold">Log Equipment Swap</button>
        </form>
    </div>

    <!-- Complete Job & Sign Off -->
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-bold text-sm text-gray-800 mb-2 border-b pb-1">4. Customer Sign Off & Complete</h2>
        <form action="{{ route('technician.work-orders.complete', $workOrder->id) }}" method="POST">
            @csrf
            <div class="mb-2">
                <label class="block text-xs font-semibold text-gray-700">Root Cause</label>
                <input type="text" name="actual_root_cause" placeholder="Faulty connector at fiber box..." class="w-full border rounded p-1 text-xs">
            </div>
            <div class="mb-2">
                <label class="block text-xs font-semibold text-gray-700">Corrective Action Taken</label>
                <input type="text" name="corrective_action" placeholder="Re-spliced connector and retested..." class="w-full border rounded p-1 text-xs">
            </div>
            <div class="mb-2">
                <label class="block text-xs font-semibold text-gray-700">Customer Name</label>
                <input type="text" name="confirmed_by_name" value="{{ $workOrder->customer ? $workOrder->customer->first_name . ' ' . $workOrder->customer->last_name : '' }}" class="w-full border rounded p-1 text-xs">
            </div>
            <button type="submit" class="w-full bg-green-600 text-white py-2 rounded text-xs font-bold hover:bg-green-700">Complete & Close Work Order</button>
        </form>
    </div>
</div>
@endsection
