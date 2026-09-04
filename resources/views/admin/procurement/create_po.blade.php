@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <div class="bg-white p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Create Purchase Order</h1>

        <form action="{{ route('admin.procurement.store-po') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                <select name="supplier_id" class="w-full border-gray-300 rounded shadow-sm text-sm" required>
                    <option value="">-- Select Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->legal_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Receiving Warehouse *</label>
                <select name="warehouse_id" class="w-full border-gray-300 rounded shadow-sm text-sm" required>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4 border-t pt-3">
                <h3 class="font-bold text-sm text-gray-700 mb-2">Order Line Item</h3>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <input type="number" name="items[0][item_id]" value="1" class="border-gray-300 rounded" placeholder="Item ID" required>
                    <input type="number" step="0.01" name="items[0][ordered_qty]" value="100" class="border-gray-300 rounded" placeholder="Ordered Qty" required>
                    <input type="number" step="0.01" name="items[0][unit_price]" value="15.00" class="border-gray-300 rounded" placeholder="Unit Price" required>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t pt-4">
                <a href="{{ route('admin.procurement.index') }}" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50 text-sm">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm">Issue Purchase Order</button>
            </div>
        </form>
    </div>
</div>
@endsection
