@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Invoices & Financial Billing Documents</h1>
            <p class="text-sm text-gray-600">Official subscriber invoices, line items, finalization & posting status.</p>
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
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Customer / Account</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Invoice Date</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($invoices as $inv)
                    <tr>
                        <td class="px-6 py-4 font-bold text-blue-600">
                            <a href="{{ route('admin.finance.invoices.show', $inv) }}">{{ $inv->invoice_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            {{ $inv->customer->full_name ?? ($inv->customer->first_name . ' ' . $inv->customer->last_name) }}
                            <span class="block text-gray-500">Acc: {{ $inv->serviceAccount->account_number ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 text-xs">{{ $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : 'N/A' }}</td>
                        <td class="px-6 py-4 text-xs font-semibold text-red-600">{{ $inv->due_date ? $inv->due_date->format('Y-m-d') : 'N/A' }}</td>
                        <td class="px-6 py-4 font-mono font-bold">PHP {{ number_format($inv->total_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                {{ $inv->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-indigo-600 hover:text-indigo-900 font-medium text-xs">
                            <a href="{{ route('admin.finance.invoices.show', $inv) }}">Detail / Print</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No invoices found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection
