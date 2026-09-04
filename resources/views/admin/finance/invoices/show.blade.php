@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Invoice: {{ $invoice->invoice_number }}</h1>
            <p class="text-sm text-gray-600">Customer: {{ $invoice->customer->full_name ?? $invoice->customer->first_name . ' ' . $invoice->customer->last_name }} | Status: <span class="font-semibold text-blue-600">{{ $invoice->status }}</span></p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.finance.invoices.pdf', $invoice) }}" target="_blank" class="px-3 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-xs font-bold">
                Download / Print PDF
            </a>
            @if($invoice->status !== 'FINALIZED' && $invoice->status !== 'CANCELLED')
                <form action="{{ route('admin.finance.invoices.finalize', $invoice) }}" method="POST" onsubmit="return confirm('Finalize invoice and post debit to authoritative ledger?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-xs font-bold">
                        Finalize & Post to Ledger
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Customer & Service Account</h3>
            <p class="text-xs mb-1"><strong>Customer #:</strong> {{ $invoice->customer->customer_number }}</p>
            <p class="text-xs mb-1"><strong>Account #:</strong> {{ $invoice->serviceAccount->account_number ?? 'N/A' }}</p>
            <p class="text-xs mb-1"><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('Y-m-d') }}</p>
            <p class="text-xs mb-1"><strong>Due Date:</strong> <span class="text-red-600 font-bold">{{ $invoice->due_date->format('Y-m-d') }}</span></p>
        </div>

        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Financial Totals</h3>
            <p class="text-xs mb-1"><strong>Subtotal:</strong> PHP {{ number_format($invoice->subtotal, 2) }}</p>
            <p class="text-xs mb-1"><strong>Tax Amount:</strong> PHP {{ number_format($invoice->tax_amount, 2) }}</p>
            <p class="text-xs mb-1"><strong>Total Amount:</strong> <span class="font-bold font-mono text-sm">PHP {{ number_format($invoice->total_amount, 2) }}</span></p>
            <p class="text-xs mb-1"><strong>Amount Due:</strong> <span class="font-bold font-mono text-sm text-red-600">PHP {{ number_format($invoice->amount_due, 2) }}</span></p>
        </div>

        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Authoritative Ledger Status</h3>
            @if($invoice->finalized_at)
                <p class="text-xs text-green-700 font-bold mb-1">POSTED TO LEDGER</p>
                <p class="text-xs text-gray-500">Finalized at {{ $invoice->finalized_at->format('Y-m-d H:i:s') }}</p>
            @else
                <p class="text-xs text-yellow-600 font-bold">UNPOSTED DRAFT INVOICE</p>
            @endif
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="bg-white rounded-lg shadow p-5 mb-6">
        <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Invoice Line Items</h3>
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50 border-b"><th class="p-2 text-left">Description</th><th class="p-2 text-left">Type</th><th class="p-2 text-left">Qty</th><th class="p-2 text-left">Unit Price</th><th class="p-2 text-left">Tax</th><th class="p-2 text-left">Total</th></tr>
            </thead>
            <tbody>
                @foreach($invoice->lines as $line)
                    <tr class="border-b">
                        <td class="p-2 font-medium">{{ $line->description }}</td>
                        <td class="p-2 font-bold">{{ $line->charge_type }}</td>
                        <td class="p-2">{{ number_format($line->quantity, 2) }}</td>
                        <td class="p-2 font-mono">PHP {{ number_format($line->unit_price, 2) }}</td>
                        <td class="p-2 font-mono">PHP {{ number_format($line->tax_amount, 2) }}</td>
                        <td class="p-2 font-mono font-bold">PHP {{ number_format($line->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
