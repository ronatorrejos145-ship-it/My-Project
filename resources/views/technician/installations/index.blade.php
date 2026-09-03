@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4 max-w-md">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Technician Dispatch Queue</h1>
            <p class="text-xs text-gray-600">Assigned Field Installation Work Orders</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded mb-3 text-xs">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-3">
        @forelse($jobs as $job)
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-600">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <span class="text-xs font-bold text-blue-600 block">{{ $job->work_order_number }}</span>
                        <h3 class="font-bold text-gray-800 text-sm">{{ $job->customer->full_name ?? $job->customer->first_name . ' ' . $job->customer->last_name }}</h3>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                        {{ $job->status }}
                    </span>
                </div>
                <p class="text-xs text-gray-600 mb-2">Package: <strong>{{ $job->package->name ?? 'N/A' }}</strong></p>
                <p class="text-xs text-gray-500 mb-3 font-mono">GPS: {{ $job->latitude }}, {{ $job->longitude }}</p>

                <a href="{{ route('technician.installations.show', $job) }}" class="block text-center py-2 bg-blue-600 text-white rounded font-bold text-xs hover:bg-blue-700">
                    Open Field Work Order
                </a>
            </div>
        @empty
            <div class="bg-white p-6 rounded text-center text-gray-500 text-sm">
                No assigned installation jobs for today.
            </div>
        @endforelse
    </div>
</div>
@endsection
