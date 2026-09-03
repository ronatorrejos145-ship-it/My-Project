@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Generated Billable Charges</h1>
            <p class="text-sm text-gray-600">Calculated charge line items ready for Phase 13 Invoicing.</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Charge #</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Customer / Account</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($charges as $chg)
                    <tr>
                        <td class="px-6 py-4 font-bold text-blue-600">{{ $chg->charge_number }}</td>
                        <td class="px-6 py-4 text-xs font-medium">
                            {{ $chg->customer->full_name ?? ($chg->customer->first_name . ' ' . $chg->customer->last_name) }}
                            <span class="block text-gray-500">Acc: {{ $chg->serviceAccount->account_number ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 text-xs font-bold">{{ $chg->charge_type }}</td>
                        <td class="px-6 py-4 text-xs text-gray-600">{{ $chg->description }}</td>
                        <td class="px-6 py-4 font-mono font-bold text-green-600">PHP {{ number_format($chg->total_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                {{ $chg->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No billable charges found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">
            {{ $charges->links() }}
        </div>
    </div>
</div>
@endsection
