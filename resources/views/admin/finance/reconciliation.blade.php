@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Financial Reconciliation & Integrity Check</h1>
            <p class="text-sm text-gray-600">Reconcile Phase 12 billable charges against Phase 13 invoice lines and ledger postings.</p>
        </div>
    </div>

    @if($recon['reconciled'])
        <div class="bg-green-100 border border-green-400 text-green-800 p-4 rounded-lg mb-6">
            <h3 class="font-bold text-base">Perfect Financial Reconciliation Achieved!</h3>
            <p class="text-xs">All Phase 12 billable charges match Phase 13 invoice lines and authoritative ledger postings without discrepancies.</p>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-300 p-4 rounded-lg mb-6">
            <h3 class="font-bold text-yellow-800 text-sm">Discrepancies Detected ({{ $recon['mismatch_count'] }})</h3>
            <div class="mt-3 space-y-2 text-xs">
                @foreach($recon['mismatches'] as $mismatch)
                    <div class="p-3 bg-white rounded border border-yellow-200">
                        <span class="font-bold text-red-600 block">{{ $mismatch['type'] }}</span>
                        <p class="text-gray-700">{{ $mismatch['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
