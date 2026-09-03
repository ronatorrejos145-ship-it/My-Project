@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4 max-w-md">
    <div class="bg-white p-5 rounded-lg shadow">
        <div class="text-center mb-4">
            <span class="px-2 py-1 text-xs font-bold bg-blue-100 text-blue-800 rounded-full">{{ $asset->current_status }}</span>
            <h1 class="text-xl font-bold text-gray-800 mt-2">{{ $asset->asset_tag }}</h1>
            <p class="text-xs text-gray-600">{{ $asset->category->name ?? 'Equipment Asset' }}</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded mb-3 text-xs">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-gray-50 p-3 rounded mb-4 text-xs space-y-1">
            <p><strong>Manufacturer:</strong> {{ $asset->manufacturer ?? 'N/A' }}</p>
            <p><strong>Serial #:</strong> <span class="font-mono font-bold">{{ $asset->serial_number ?? 'N/A' }}</span></p>
            <p><strong>MAC Address:</strong> <span class="font-mono font-bold">{{ $asset->mac_address ?? 'N/A' }}</span></p>
            <p><strong>Location:</strong> {{ $asset->current_location ?? 'Warehouse' }}</p>
        </div>

        <h3 class="font-bold text-sm text-gray-700 border-b pb-1 mb-3">Field Verification Audit</h3>

        <form action="{{ route('assets.qr.verify', $asset) }}" method="POST" class="text-xs space-y-3">
            @csrf

            <div>
                <label class="block font-medium text-gray-700 mb-1">Physical Presence *</label>
                <select name="physical_presence" class="w-full border-gray-300 rounded text-xs" required>
                    <option value="FOUND">FOUND & VERIFIED</option>
                    <option value="NOT_FOUND">NOT FOUND (MISSING)</option>
                    <option value="WRONG_LOCATION">WRONG LOCATION</option>
                    <option value="DISCREPANCY">SERIAL/MAC MISMATCH</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-gray-700 mb-1">Condition *</label>
                <select name="condition" class="w-full border-gray-300 rounded text-xs" required>
                    <option value="NEW" {{ $asset->condition === 'NEW' ? 'selected' : '' }}>NEW</option>
                    <option value="GOOD" {{ $asset->condition === 'GOOD' ? 'selected' : '' }}>GOOD</option>
                    <option value="FAIR" {{ $asset->condition === 'FAIR' ? 'selected' : '' }}>FAIR</option>
                    <option value="POOR" {{ $asset->condition === 'POOR' ? 'selected' : '' }}>POOR</option>
                    <option value="DAMAGED" {{ $asset->condition === 'DAMAGED' ? 'selected' : '' }}>DAMAGED</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-gray-700 mb-1">Audit Notes</label>
                <textarea name="notes" rows="2" class="w-full border-gray-300 rounded text-xs" placeholder="Field remarks..."></textarea>
            </div>

            <button type="submit" class="w-full py-2 bg-blue-600 text-white font-bold rounded text-xs hover:bg-blue-700">
                Submit Physical Verification
            </button>
        </form>
    </div>
</div>
@endsection
