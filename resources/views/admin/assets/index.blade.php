@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Technical Assets & Serialized Equipment</h1>
            <p class="text-sm text-gray-600">Track lifecycle, serials, MAC addresses, assignments & warranties.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.assets.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm">
                + Receive Asset
            </a>
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
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Asset Tag</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Serial Number</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">MAC Address</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Location / Assignee</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($assets as $ast)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-blue-600">
                            <a href="{{ route('admin.assets.show', $ast) }}">{{ $ast->asset_tag }}</a>
                        </td>
                        <td class="px-6 py-4">{{ $ast->category->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 font-mono text-xs">{{ $ast->serial_number ?? 'N/A' }}</td>
                        <td class="px-6 py-4 font-mono text-xs">{{ $ast->mac_address ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                {{ $ast->current_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-600">{{ $ast->current_location ?? 'Warehouse' }}</td>
                        <td class="px-6 py-4 text-indigo-600 hover:text-indigo-900 font-medium">
                            <a href="{{ route('admin.assets.show', $ast) }}">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No equipment assets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">
            {{ $assets->links() }}
        </div>
    </div>
</div>
@endsection
