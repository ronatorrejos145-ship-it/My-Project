@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Authoritative Financial Ledger</h1>
            <p class="text-sm text-gray-600">Double-entry debit and credit transactions, immutable posting history & customer balances.</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Transaction #</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Customer / Account</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Debit (+)</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Credit (-)</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($transactions as $tx)
                    <tr>
                        <td class="px-6 py-4 font-bold text-blue-600">{{ $tx->transaction_number }}</td>
                        <td class="px-6 py-4 text-xs font-medium">
                            {{ $tx->customer->full_name ?? 'N/A' }}
                            <span class="block text-gray-500">Acc: {{ $tx->serviceAccount->account_number ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 text-xs font-bold">{{ $tx->transaction_type }}</td>
                        <td class="px-6 py-4 text-xs text-gray-600">{{ $tx->description }}</td>
                        <td class="px-6 py-4 font-mono text-red-600 font-bold">
                            {{ $tx->debit_amount > 0 ? 'PHP ' . number_format($tx->debit_amount, 2) : '-' }}
                        </td>
                        <td class="px-6 py-4 font-mono text-green-600 font-bold">
                            {{ $tx->credit_amount > 0 ? 'PHP ' . number_format($tx->credit_amount, 2) : '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">
                                {{ $tx->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No ledger transactions posted yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
