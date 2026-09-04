@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Inventory Stock Balances</h1>
            <p class="text-sm text-gray-600">Stock on hand, reservations & location balances.</p>
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
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">SKU / Item</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Warehouse</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">On Hand</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Reserved</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Available</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($balances as $bal)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-gray-800">
                            {{ $bal->item->name ?? 'N/A' }} <span class="text-xs text-gray-500 font-mono">({{ $bal->item->sku ?? 'N/A' }})</span>
                        </td>
                        <td class="px-6 py-4">{{ $bal->warehouse->name ?? 'Main' }}</td>
                        <td class="px-6 py-4 font-bold text-blue-600">{{ number_format($bal->quantity_on_hand, 2) }} {{ $bal->item->unit ?? 'pcs' }}</td>
                        <td class="px-6 py-4 text-yellow-600">{{ number_format($bal->quantity_reserved, 2) }}</td>
                        <td class="px-6 py-4 font-bold text-green-600">{{ number_format($bal->quantity_on_hand - $bal->quantity_reserved, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No stock balances found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">
            {{ $balances->links() }}
        </div>
    </div>
</div>
@endsection
