@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Technician Field Jobs</h1>
        <p class="text-sm text-gray-600">Mobile Field Dispatch Workbench</p>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse($assignedJobs as $job)
            <div class="bg-white rounded-lg shadow p-4 border-l-4
                {{ $job->status === 'IN_PROGRESS' ? 'border-green-600' : 'border-indigo-600' }}">
                <div class="flex justify-between items-start mb-2">
                    <span class="font-bold text-sm text-indigo-600">{{ $job->work_order_number }}</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-yellow-100 text-yellow-800">{{ $job->status }}</span>
                </div>
                <h2 class="font-bold text-base text-gray-900 mb-1">{{ $job->title }}</h2>
                <p class="text-xs text-gray-600 mb-2">{{ $job->customer ? $job->customer->first_name . ' ' . $job->customer->last_name : 'No Customer' }} - {{ $job->service_address ?? 'No Address' }}</p>

                <div class="flex space-x-2 mt-3">
                    <a href="{{ route('technician.work-orders.show', $job->id) }}" class="block text-center w-full bg-indigo-600 text-white py-2 rounded text-xs font-bold hover:bg-indigo-700">Open Job Workspace</a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
                No active jobs assigned to you at the moment.
            </div>
        @endforelse
    </div>
</div>
@endsection
