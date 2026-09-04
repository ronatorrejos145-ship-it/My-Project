@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-1 rounded">
                {{ $workOrder->work_order_type }}
            </span>
            <h1 class="text-3xl font-bold text-gray-800 mt-1">{{ $workOrder->work_order_number }} - {{ $workOrder->title }}</h1>
            <p class="text-sm text-gray-600">Created {{ $workOrder->created_at->diffForHumans() }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.maintenance.work-orders.index') }}" class="px-4 py-2 border rounded text-sm font-medium">Back to List</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Details Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Work Order Details</h2>
                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div>
                        <span class="text-xs text-gray-500 uppercase block">Status</span>
                        <span class="font-bold text-indigo-600">{{ $workOrder->status }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase block">Priority / Severity</span>
                        <span class="font-bold text-red-600">{{ $workOrder->priority }} / {{ $workOrder->severity }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase block">Customer</span>
                        <span class="font-medium text-gray-900">{{ $workOrder->customer ? $workOrder->customer->first_name . ' ' . $workOrder->customer->last_name : 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase block">Service Address</span>
                        <span class="font-medium text-gray-900">{{ $workOrder->service_address ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="mb-4">
                    <span class="text-xs text-gray-500 uppercase block mb-1">Description</span>
                    <p class="text-sm text-gray-800 bg-gray-50 p-3 rounded border">{{ $workOrder->description ?? 'No description provided.' }}</p>
                </div>
            </div>

            <!-- Diagnostics & Measurements -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Technical Diagnostics</h2>
                @forelse($workOrder->diagnostics as $diag)
                    <div class="bg-gray-50 p-4 rounded border mb-3 text-sm grid grid-cols-3 gap-2">
                        <div><span class="text-xs text-gray-500 block">RX Power</span> <strong>{{ $diag->rx_power_dbm ?? 'N/A' }} dBm</strong></div>
                        <div><span class="text-xs text-gray-500 block">Download Speed</span> <strong>{{ $diag->download_speed_mbps ?? 'N/A' }} Mbps</strong></div>
                        <div><span class="text-xs text-gray-500 block">Upload Speed</span> <strong>{{ $diag->upload_speed_mbps ?? 'N/A' }} Mbps</strong></div>
                        <div><span class="text-xs text-gray-500 block">Latency</span> <strong>{{ $diag->latency_ms ?? 'N/A' }} ms</strong></div>
                        <div><span class="text-xs text-gray-500 block">Cable Condition</span> <strong>{{ $diag->cable_condition ?? 'N/A' }}</strong></div>
                        <div><span class="text-xs text-gray-500 block">Connector Condition</span> <strong>{{ $diag->connector_condition ?? 'N/A' }}</strong></div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No diagnostic measurements recorded yet.</p>
                @endforelse
            </div>

            <!-- Materials & Equipment Replacements -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Consumed Materials & Replacements</h2>
                <div class="mb-4">
                    <h3 class="font-semibold text-sm text-gray-700 mb-2">Consumed Inventory Items</h3>
                    <ul class="divide-y text-sm">
                        @forelse($workOrder->materials as $mat)
                            <li class="py-2 flex justify-between">
                                <span>{{ $mat->item->name }} (Qty: {{ $mat->consumed_quantity }})</span>
                                <span class="font-bold">₱{{ number_format($mat->total_cost, 2) }}</span>
                            </li>
                        @empty
                            <li class="py-2 text-gray-500 text-xs">No inventory items consumed yet.</li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold text-sm text-gray-700 mb-2">Equipment Swaps</h3>
                    @forelse($workOrder->equipmentReplacements as $rep)
                        <div class="bg-indigo-50 p-3 rounded border text-xs text-indigo-900 mb-2">
                            Swapped Serial <strong>{{ $rep->old_serial_number ?? 'Old Asset' }}</strong> with New Serial <strong>{{ $rep->new_serial_number ?? 'New Asset' }}</strong>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500">No equipment replacements recorded.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Assignment Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Dispatch & Assignment</h2>
                <div class="mb-4">
                    <span class="text-xs text-gray-500 uppercase block mb-1">Assigned Technician</span>
                    <span class="font-bold text-gray-900 text-base">{{ $workOrder->assignedTechnician ? $workOrder->assignedTechnician->name : 'Unassigned' }}</span>
                </div>

                <form action="{{ route('admin.maintenance.dispatch.assign', $workOrder->id) }}" method="POST">
                    @csrf
                    <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Reassign Technician</label>
                    <select name="technician_id" class="w-full border rounded px-3 py-2 text-sm mb-3">
                        @foreach($technicians as $t)
                            <option value="{{ $t->id }}" {{ $workOrder->assigned_technician_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded text-sm font-medium hover:bg-indigo-700">Update Assignment</button>
                </form>
            </div>

            <!-- Status Transition Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Status Lifecycle</h2>
                <form action="{{ route('admin.maintenance.work-orders.update-status', $workOrder->id) }}" method="POST">
                    @csrf
                    <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Target Status</label>
                    <select name="status" class="w-full border rounded px-3 py-2 text-sm mb-3">
                        <option value="PENDING">PENDING</option>
                        <option value="ASSIGNED">ASSIGNED</option>
                        <option value="SCHEDULED">SCHEDULED</option>
                        <option value="EN_ROUTE">EN_ROUTE</option>
                        <option value="ON_SITE">ON_SITE</option>
                        <option value="IN_PROGRESS">IN_PROGRESS</option>
                        <option value="TESTING">TESTING</option>
                        <option value="COMPLETED">COMPLETED</option>
                        <option value="CLOSED">CLOSED</option>
                        <option value="CANCELLED">CANCELLED</option>
                    </select>
                    <button type="submit" class="w-full bg-gray-800 text-white py-2 rounded text-sm font-medium hover:bg-gray-900">Transition Status</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
