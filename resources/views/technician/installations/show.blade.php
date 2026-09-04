@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4 max-w-lg">
    <div class="mb-4">
        <a href="{{ route('technician.installations.index') }}" class="text-xs text-blue-600 font-bold">&larr; Back to Queue</a>
        <h1 class="text-xl font-bold text-gray-800">Job: {{ $installation->work_order_number }}</h1>
        <p class="text-xs text-gray-600">Customer: {{ $installation->customer->full_name ?? $installation->customer->first_name . ' ' . $installation->customer->last_name }}</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded mb-3 text-xs">
            {{ session('success') }}
        </div>
    @endif

    <!-- Status Actions -->
    <div class="bg-white p-4 rounded-lg shadow mb-4">
        <h3 class="font-bold text-sm text-gray-700 mb-2">Step 1: Dispatch & GPS Arrival</h3>
        <p class="text-xs text-gray-500 mb-3">Status: <span class="font-bold text-blue-600">{{ $installation->status }}</span></p>

        <div class="flex gap-2">
            @if(in_array($installation->status, ['ASSIGNED', 'SCHEDULED', 'PENDING']))
                <form action="{{ route('technician.installations.dispatch-enroute', $installation) }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="w-full py-2 bg-yellow-500 text-white font-bold rounded text-xs">Mark En Route</button>
                </form>
            @endif

            @if(in_array($installation->status, ['EN_ROUTE', 'ASSIGNED', 'SCHEDULED', 'PENDING']))
                <form action="{{ route('technician.installations.arrive', $installation) }}" method="POST" class="w-full">
                    @csrf
                    <input type="hidden" name="latitude" value="{{ $installation->latitude ?? 14.5995 }}">
                    <input type="hidden" name="longitude" value="{{ $installation->longitude ?? 120.9842 }}">
                    <button type="submit" class="w-full py-2 bg-blue-600 text-white font-bold rounded text-xs">Verify On Site GPS Arrival</button>
                </form>
            @endif
        </div>
    </div>

    <!-- Technical Test Execution -->
    <div class="bg-white p-4 rounded-lg shadow mb-4">
        <h3 class="font-bold text-sm text-gray-700 mb-2">Step 2: Field Technical Tests</h3>
        <form action="{{ route('technician.installations.record-test', $installation) }}" method="POST" class="text-xs space-y-2">
            @csrf
            <div>
                <label class="block text-gray-600">Test Type</label>
                <select name="test_type" class="w-full border-gray-300 rounded text-xs">
                    <option value="DOWNLOAD">DOWNLOAD SPEED (Mbps)</option>
                    <option value="UPLOAD">UPLOAD SPEED (Mbps)</option>
                    <option value="LATENCY">LATENCY (ms)</option>
                    <option value="SIGNAL">OPTICAL SIGNAL (dBm)</option>
                    <option value="CONNECTIVITY">INTERNET REACHABILITY</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-600">Measured Value</label>
                <input type="text" name="measured_value" placeholder="e.g. 50.5" class="w-full border-gray-300 rounded text-xs" required>
            </div>
            <button type="submit" class="w-full py-2 bg-indigo-600 text-white font-bold rounded text-xs">Log Test Result</button>
        </form>

        <div class="mt-3 border-t pt-2">
            <h4 class="font-bold text-xs text-gray-600 mb-1">Logged Tests:</h4>
            @foreach($installation->tests as $test)
                <div class="text-xs flex justify-between py-1 border-b">
                    <span>{{ $test->test_type }}: <strong>{{ $test->measured_value }}</strong></span>
                    <span class="font-bold text-green-600">{{ $test->result }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Customer Acceptance Handover -->
    <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="font-bold text-sm text-gray-700 mb-2">Step 3: Customer Sign-off Link</h3>
        <a href="{{ route('customer.installations.acceptance', $installation) }}" target="_blank" class="block w-full py-2 bg-green-600 text-white text-center font-bold rounded text-xs hover:bg-green-700">
            Open Customer Sign-off Portal
        </a>
    </div>
</div>
@endsection
