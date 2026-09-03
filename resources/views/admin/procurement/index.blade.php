@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Procurement & Purchase Orders</h1>
            <p class="text-sm text-gray-600">Manage purchase requests, supplier POs & goods receiving.</p>
        </div>
        <a href="{{ route('admin.procurement.create-po') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm">
            + Create Purchase Order
        </a>
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
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($purchaseOrders as $po)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-blue-600">{{ $po->po_number }}</td>
                        <td class="px-6 py-4">{{ $po->supplier->legal_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 font-mono font-bold">PHP {{ number_format($po->total_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                {{ $po->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-xs">{{ $po->order_date ? $po->order_date->format('Y-m-d') : 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No purchase orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">
            {{ $purchaseOrders->links() }}
        </div>
    </div>
</div>
@endsection
