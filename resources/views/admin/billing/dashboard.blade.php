@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Billing Engine Operations Dashboard</h1>
            <p class="text-sm text-gray-600">Deterministic calculation engine, recurring charge runs & proration breakdowns.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.billing.runs') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm">
                Billing Runs
            </a>
            <a href="{{ route('admin.billing.charges') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 font-medium text-sm">
                Billable Charges
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-500 text-xs uppercase mb-1">Generated Charges (Uninvoiced)</h3>
            <p class="text-2xl font-bold font-mono text-blue-600">PHP {{ number_format($totalChargesAmount, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $totalChargesCount }} total charge records ready for Phase 13 Invoicing</p>
        </div>
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-500 text-xs uppercase mb-1">Open Billing Exceptions</h3>
            <p class="text-2xl font-bold font-mono text-yellow-600">{{ $openExceptions->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Calculation or profile exception alerts</p>
        </div>
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-500 text-xs uppercase mb-1">Interactive Tools</h3>
            <a href="{{ route('admin.billing.proration-calculator') }}" class="block mt-2 text-center py-2 bg-gray-800 text-white font-bold rounded text-xs hover:bg-gray-900">
                Open Proration Calculator
            </a>
        </div>
    </div>

    <!-- Recent Billing Runs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b bg-gray-50 font-bold text-gray-700 text-sm">Recent Billing Runs</div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Run #</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Billing Date</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Accounts Billed</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($recentRuns as $run)
                    <tr>
                        <td class="px-6 py-4 font-bold text-blue-600">{{ $run->run_number }}</td>
                        <td class="px-6 py-4 text-xs">{{ $run->billing_date ? $run->billing_date->format('Y-m-d') : 'N/A' }}</td>
                        <td class="px-6 py-4 text-xs">{{ $run->successful_accounts }} / {{ $run->total_accounts }}</td>
                        <td class="px-6 py-4 font-mono font-bold">PHP {{ number_format($run->total_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">
                                {{ $run->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No billing runs executed yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
