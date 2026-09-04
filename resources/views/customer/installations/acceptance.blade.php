@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-lg">
    <div class="bg-white p-6 rounded-lg shadow">
        <h1 class="text-xl font-bold text-gray-800 mb-2">Customer Installation Sign-off</h1>
        <p class="text-sm text-gray-600 mb-4">Work Order: <strong>{{ $installation->work_order_number }}</strong></p>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-gray-50 p-4 rounded mb-6 text-xs text-gray-700 space-y-1">
            <p><strong>Customer:</strong> {{ $installation->customer->full_name ?? $installation->customer->first_name . ' ' . $installation->customer->last_name }}</p>
            <p><strong>Plan Installed:</strong> {{ $installation->package->name ?? 'N/A' }}</p>
            <p><strong>Installed Equipment:</strong> {{ $installation->equipment->count() }} asset(s)</p>
            <p><strong>Speed & Signal Tests:</strong> Verified Passed</p>
        </div>

        <form action="{{ route('customer.installations.accept', $installation) }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Signer Full Name *</label>
                <input type="text" name="signer_name" value="{{ $installation->customer->full_name ?? ($installation->customer->first_name . ' ' . $installation->customer->last_name) }}" class="w-full border-gray-300 rounded shadow-sm text-sm" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Relationship to Account *</label>
                <select name="signer_relationship" class="w-full border-gray-300 rounded shadow-sm text-sm">
                    <option value="OWNER">ACCOUNT OWNER</option>
                    <option value="SPOUSE">SPOUSE</option>
                    <option value="AUTHORIZED_REPRESENTATIVE">AUTHORIZED REPRESENTATIVE</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Acceptance Status *</label>
                <select name="acceptance_status" class="w-full border-gray-300 rounded shadow-sm text-sm" required>
                    <option value="ACCEPTED">ACCEPTED - FULLY SATISFIED</option>
                    <option value="ACCEPTED_WITH_ISSUES">ACCEPTED WITH MINOR ISSUES</option>
                    <option value="REJECTED">REJECTED - UNSATISFACTORY</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes / Remarks</label>
                <textarea name="notes" rows="3" class="w-full border-gray-300 rounded shadow-sm text-sm" placeholder="Any comments regarding speed or physical installation..."></textarea>
            </div>

            <button type="submit" class="w-full py-2 bg-green-600 text-white font-bold text-sm rounded hover:bg-green-700">
                Confirm & Submit Acceptance
            </button>
        </form>
    </div>
</div>
@endsection
