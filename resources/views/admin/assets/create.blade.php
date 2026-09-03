@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <div class="bg-white p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Receive Serialized Asset</h1>

        <form action="{{ route('admin.assets.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                <select name="asset_category_id" class="w-full border-gray-300 rounded shadow-sm text-sm" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number</label>
                    <input type="text" name="serial_number" placeholder="SN-XXXXXX" class="w-full border-gray-300 rounded shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MAC Address</label>
                    <input type="text" name="mac_address" placeholder="AA:BB:CC:DD:EE:FF" class="w-full border-gray-300 rounded shadow-sm text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer</label>
                    <input type="text" name="manufacturer" placeholder="MikroTik / TP-Link / Huawei" class="w-full border-gray-300 rounded shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Cost (PHP)</label>
                    <input type="number" step="0.01" name="purchase_cost" placeholder="0.00" class="w-full border-gray-300 rounded shadow-sm text-sm">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Receiving Warehouse</label>
                <select name="warehouse_id" class="w-full border-gray-300 rounded shadow-sm text-sm">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Condition</label>
                <select name="condition" class="w-full border-gray-300 rounded shadow-sm text-sm">
                    <option value="NEW">NEW</option>
                    <option value="GOOD">GOOD</option>
                    <option value="FAIR">FAIR</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full border-gray-300 rounded shadow-sm text-sm" placeholder="Receiving details..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.assets.index') }}" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50 text-sm">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm">Receive Asset</button>
            </div>
        </form>
    </div>
</div>
@endsection
