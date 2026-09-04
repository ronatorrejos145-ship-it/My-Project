@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Subscriber: {{ $subscriber->account_number }}</h1>
            <p class="text-sm text-gray-600">Customer: {{ $subscriber->customer->full_name ?? $subscriber->customer->first_name . ' ' . $subscriber->customer->last_name }} | Status: <span class="font-semibold text-green-600">{{ $subscriber->status }}</span></p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-3 gap-6 mb-6">
        <!-- Active Plan Snapshot Card -->
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Active Subscription Plan</h3>
            @if($subscriber->currentSubscription)
                <p class="text-xs mb-1"><strong>Plan:</strong> {{ $subscriber->currentSubscription->package_name_snapshot }}</p>
                <p class="text-xs mb-1"><strong>Speed:</strong> {{ $subscriber->currentSubscription->download_speed_snapshot }} Mbps Down / {{ $subscriber->currentSubscription->upload_speed_snapshot }} Mbps Up</p>
                <p class="text-xs mb-1"><strong>Monthly Rate:</strong> <span class="font-bold font-mono">PHP {{ number_format($subscriber->currentSubscription->monthly_price_snapshot, 2) }}</span></p>
                <p class="text-xs mb-1"><strong>Billing Cycle:</strong> {{ $subscriber->currentSubscription->billing_cycle_snapshot }}</p>
            @else
                <p class="text-xs text-gray-500">No active subscription record.</p>
            @endif
        </div>

        <!-- Plan Upgrade / Downgrade Card -->
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Change Package / Plan</h3>
            @if($subscriber->currentSubscription)
                <form action="{{ route('admin.subscribers.change-package', $subscriber->currentSubscription) }}" method="POST" class="text-xs space-y-2">
                    @csrf
                    <div>
                        <label class="block text-gray-600">New Package *</label>
                        <select name="package_id" class="w-full border-gray-300 rounded text-xs" required>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="package_version_id" value="1">
                    <div>
                        <label class="block text-gray-600">Change Type *</label>
                        <select name="change_type" class="w-full border-gray-300 rounded text-xs" required>
                            <option value="PACKAGE_UPGRADE">PACKAGE_UPGRADE</option>
                            <option value="PACKAGE_DOWNGRADE">PACKAGE_DOWNGRADE</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-1.5 bg-blue-600 text-white rounded font-bold">Apply Plan Change</button>
                </form>
            @endif
        </div>

        <!-- Status Management Card -->
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Subscription Lifecycle Status</h3>
            @if($subscriber->currentSubscription)
                <form action="{{ route('admin.subscribers.update-status', $subscriber->currentSubscription) }}" method="POST" class="text-xs space-y-2">
                    @csrf
                    <div>
                        <label class="block text-gray-600">Lifecycle Status *</label>
                        <select name="status" class="w-full border-gray-300 rounded text-xs" required>
                            <option value="ACTIVE" {{ $subscriber->currentSubscription->status === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                            <option value="GRACE" {{ $subscriber->currentSubscription->status === 'GRACE' ? 'selected' : '' }}>GRACE</option>
                            <option value="SUSPENDED" {{ $subscriber->currentSubscription->status === 'SUSPENDED' ? 'selected' : '' }}>SUSPENDED</option>
                            <option value="TERMINATED" {{ $subscriber->currentSubscription->status === 'TERMINATED' ? 'selected' : '' }}>TERMINATED</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600">Reason</label>
                        <input type="text" name="reason" placeholder="State change reason..." class="w-full border-gray-300 rounded text-xs">
                    </div>
                    <button type="submit" class="w-full py-1.5 bg-indigo-600 text-white rounded font-bold">Update Status</button>
                </form>
            @endif
        </div>
    </div>

    <!-- Contract & Service Request History -->
    <div class="grid grid-cols-2 gap-6">
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Service Contracts</h3>
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b"><th class="text-left p-2">Contract #</th><th class="text-left p-2">Monthly Fee</th><th class="text-left p-2">Status</th></tr>
                </thead>
                <tbody>
                    @forelse($subscriber->contracts as $contract)
                        <tr class="border-b">
                            <td class="p-2 font-bold">{{ $contract->contract_number }}</td>
                            <td class="p-2 font-mono">PHP {{ number_format($contract->monthly_fee, 2) }}</td>
                            <td class="p-2"><span class="font-bold text-green-600">{{ $contract->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-2 text-gray-500">No active contracts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Service Requests</h3>
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b"><th class="text-left p-2">Type</th><th class="text-left p-2">Status</th><th class="text-left p-2">Date</th></tr>
                </thead>
                <tbody>
                    @forelse($subscriber->serviceRequests as $req)
                        <tr class="border-b">
                            <td class="p-2 font-bold">{{ $req->request_type }}</td>
                            <td class="p-2">{{ $req->status }}</td>
                            <td class="p-2 text-gray-500">{{ $req->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-2 text-gray-500">No service requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
