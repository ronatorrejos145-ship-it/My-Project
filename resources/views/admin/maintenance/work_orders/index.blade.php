@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Field Work Orders</h1>
            <p class="text-sm text-gray-600">Manage corrective, preventive, and emergency field service operations.</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.maintenance.dispatch.workbench') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-medium hover:bg-indigo-700">Dispatch Workbench</a>
            <a href="{{ route('admin.maintenance.work-orders.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-md font-medium hover:bg-green-700">+ New Work Order</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <form method="GET" class="flex space-x-3 w-full md:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search WO #, Customer..." class="border rounded px-3 py-2 text-sm w-64">
                <select name="status" class="border rounded px-3 py-2 text-sm">
                    <option value="">All Statuses</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pending</option>
                    <option value="ASSIGNED" {{ request('status') == 'ASSIGNED' ? 'selected' : '' }}>Assigned</option>
                    <option value="IN_PROGRESS" {{ request('status') == 'IN_PROGRESS' ? 'selected' : '' }}>In Progress</option>
                    <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                    <option value="CLOSED" {{ request('status') == 'CLOSED' ? 'selected' : '' }}>Closed</option>
                </select>
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded text-sm font-medium">Filter</button>
            </form>
        </div>

        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">WO #</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Title / Customer</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Priority</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Technician</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($workOrders as $wo)
                    <tr>
                        <td class="px-6 py-4 font-bold text-indigo-600">{{ $wo->work_order_number }}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $wo->title }}</div>
                            <div class="text-xs text-gray-500">{{ $wo->customer ? $wo->customer->first_name . ' ' . $wo->customer->last_name : 'No Customer' }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold uppercase">{{ $wo->work_order_type }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded font-bold
                                {{ $wo->priority === 'CRITICAL' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $wo->priority === 'HIGH' || $wo->priority === 'URGENT' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $wo->priority === 'NORMAL' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $wo->priority === 'LOW' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ $wo->priority }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded font-bold
                                {{ in_array($wo->status, ['COMPLETED', 'CLOSED']) ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $wo->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-700">{{ $wo->assignedTechnician ? $wo->assignedTechnician->name : 'Unassigned' }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.maintenance.work-orders.show', $wo->id) }}" class="text-indigo-600 font-medium hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No work orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4">
            {{ $workOrders->links() }}
        </div>
    </div>
</div>
@endsection
