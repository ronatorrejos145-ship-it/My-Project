@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Batch Billing Runs</h1>
            <p class="text-sm text-gray-600">Execute automated recurring charge generation for active subscribers.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-5 rounded-lg shadow mb-6">
        <h3 class="font-bold text-gray-700 text-sm mb-3">Execute New Batch Billing Run</h3>
        <form action="{{ route('admin.billing.runs.execute') }}" method="POST" class="flex gap-4 items-end text-xs">
            @csrf
            <div>
                <label class="block font-medium text-gray-700 mb-1">Billing Date *</label>
                <input type="date" name="billing_date" value="{{ date('Y-m-d') }}" class="border-gray-300 rounded text-xs" required>
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Billing Cycle *</label>
                <select name="billing_cycle" class="border-gray-300 rounded text-xs" required>
                    <option value="MONTHLY">MONTHLY</option>
                    <option value="WEEKLY">WEEKLY</option>
                    <option value="DAILY">DAILY</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700">
                Execute Billing Run
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Run Number</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Billing Date</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Cycle</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Successful / Total</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($runs as $run)
                    <tr>
                        <td class="px-6 py-4 font-bold text-blue-600">{{ $run->run_number }}</td>
                        <td class="px-6 py-4 text-xs">{{ $run->billing_date ? $run->billing_date->format('Y-m-d') : 'N/A' }}</td>
                        <td class="px-6 py-4 text-xs">{{ $run->billing_cycle }}</td>
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
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No billing runs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">
            {{ $runs->links() }}
        </div>
    </div>
</div>
@endsection
