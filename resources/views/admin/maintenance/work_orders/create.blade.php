@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Create Field Work Order</h1>
        <p class="text-sm text-gray-600">Issue a new maintenance or field service work order.</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        <form action="{{ route('admin.maintenance.work-orders.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title" required class="w-full border rounded px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Customer</label>
                    <select name="customer_id" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">-- None --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }} (#{{ $c->account_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Work Order Type *</label>
                    <select name="work_order_type" required class="w-full border rounded px-3 py-2 text-sm">
                        <option value="CORRECTIVE">Corrective Repair</option>
                        <option value="PREVENTIVE">Preventive Maintenance</option>
                        <option value="EMERGENCY">Emergency Outage</option>
                        <option value="EQUIPMENT_REPLACEMENT">Equipment Replacement</option>
                        <option value="INSPECTION">Inspection</option>
                        <option value="RELOCATION_RELATED">Relocation</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Priority *</label>
                    <select name="priority" required class="w-full border rounded px-3 py-2 text-sm">
                        <option value="LOW">Low</option>
                        <option value="NORMAL" selected>Normal</option>
                        <option value="HIGH">High</option>
                        <option value="URGENT">Urgent</option>
                        <option value="CRITICAL">Critical</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Severity *</label>
                    <select name="severity" required class="w-full border rounded px-3 py-2 text-sm">
                        <option value="MINOR">Minor</option>
                        <option value="MODERATE" selected>Moderate</option>
                        <option value="MAJOR">Major</option>
                        <option value="CRITICAL">Critical</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Assign Technician</label>
                    <select name="assigned_technician_id" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">-- Unassigned --</option>
                        @foreach($technicians as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Service Address / GPS Location</label>
                <input type="text" name="service_address" placeholder="123 Main St..." class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div class="mb-6">
                <label class="block text-xs font-semibold uppercase text-gray-700 mb-1">Description / Reported Issue</label>
                <textarea name="description" rows="4" class="w-full border rounded px-3 py-2 text-sm"></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.maintenance.work-orders.index') }}" class="px-4 py-2 border rounded text-sm font-medium text-gray-700">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded text-sm font-medium hover:bg-indigo-700">Create Work Order</button>
            </div>
        </form>
    </div>
</div>
@endsection
