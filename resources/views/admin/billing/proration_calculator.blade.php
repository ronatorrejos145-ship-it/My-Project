@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <div class="bg-white p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Proration & Mid-Cycle Calculator</h1>
        <p class="text-sm text-gray-600 mb-6">Interactive deterministic proration estimator for mid-month activations, upgrades & downgrades.</p>

        <form action="{{ route('admin.billing.proration-calculator.calculate') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-medium text-gray-700 mb-1">Package Full Monthly Price (PHP) *</label>
                <input type="number" step="0.01" name="full_price" value="{{ old('full_price', '1499.00') }}" class="w-full border-gray-300 rounded text-xs" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Service Start / Effective Date *</label>
                    <input type="date" name="service_start" value="{{ old('service_start', date('Y-m-15')) }}" class="w-full border-gray-300 rounded text-xs" required>
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Proration Basis *</label>
                    <select name="basis" class="w-full border-gray-300 rounded text-xs" required>
                        <option value="CALENDAR_DAY">CALENDAR_DAY (Actual days in month)</option>
                        <option value="FIXED_30_DAY">FIXED_30_DAY (30-day fixed basis)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Period Start Date *</label>
                    <input type="date" name="period_start" value="{{ old('period_start', date('Y-m-01')) }}" class="w-full border-gray-300 rounded text-xs" required>
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Period End Date *</label>
                    <input type="date" name="period_end" value="{{ old('period_end', date('Y-m-t')) }}" class="w-full border-gray-300 rounded text-xs" required>
                </div>
            </div>

            <button type="submit" class="w-full py-2 bg-blue-600 text-white font-bold rounded text-xs hover:bg-blue-700">
                Calculate Proration
            </button>
        </form>

        @if(isset($result))
            <div class="mt-6 border-t pt-4 bg-gray-50 p-4 rounded text-xs space-y-2">
                <h3 class="font-bold text-gray-800 text-sm">Proration Calculation Result</h3>
                <p><strong>Full Base Price:</strong> PHP {{ number_format($result['full_price'], 2) }}</p>
                <p><strong>Total Days in Period:</strong> {{ $result['total_days'] }} days</p>
                <p><strong>Billable Used Days:</strong> {{ $result['used_days'] }} days (Unused: {{ $result['unused_days'] }} days)</p>
                <p><strong>Proration Factor:</strong> {{ $result['proration_factor'] }}</p>
                <div class="p-2 bg-blue-100 border border-blue-300 text-blue-800 rounded font-bold">
                    Prorated Charge Amount: PHP {{ number_format($result['prorated_amount'], 2) }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
