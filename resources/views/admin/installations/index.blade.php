@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Installation Work Orders</h1>
            <p class="text-sm text-gray-600">Manage field installations, technician dispatch, testing & customer handoffs.</p>
        </div>
        <a href="{{ route('admin.installations.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm">
            + New Work Order
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Work Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Technician</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($installations as $wo)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-blue-600">
                            <a href="{{ route('admin.installations.show', $wo) }}">{{ $wo->work_order_number }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $wo->customer->full_name ?? ($wo->customer->first_name . ' ' . $wo->customer->last_name) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $wo->package->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $wo->assignedTechnician ? ($wo->assignedTechnician->first_name . ' ' . $wo->assignedTechnician->last_name) : 'Unassigned' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                {{ $wo->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="{{ route('admin.installations.show', $wo) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">View Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No installation work orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">
            {{ $installations->links() }}
        </div>
    </div>
</div>
@endsection
