@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Technical Tools & Kits</h1>
            <p class="text-sm text-gray-600">Manage field tool checkouts, inspections & calibrations.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Tool Code</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Tool Name</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Assigned Tech</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tools as $tool)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-blue-600">{{ $tool->tool_code }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $tool->name }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $tool->category->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $tool->condition }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                {{ $tool->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-600">
                            {{ $tool->assignedEmployee ? ($tool->assignedEmployee->first_name . ' ' . $tool->assignedEmployee->last_name) : 'Unassigned' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No technical tools found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">
            {{ $tools->links() }}
        </div>
    </div>
</div>
@endsection
