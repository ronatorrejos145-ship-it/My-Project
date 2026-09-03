@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">ISP Subscribers & Service Accounts</h1>
            <p class="text-sm text-gray-600">Manage active broadband subscriptions, activations, plan upgrades & relocations.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Ready for Activation Handoffs Card -->
    @if($pendingHandoffs->count() > 0)
        <div class="bg-yellow-50 border border-yellow-300 p-4 rounded-lg mb-6">
            <h3 class="font-bold text-yellow-800 text-sm mb-2">Completed Installation Handoffs Pending Service Activation ({{ $pendingHandoffs->count() }})</h3>
            <div class="space-y-2">
                @foreach($pendingHandoffs as $handoff)
                    <div class="flex justify-between items-center bg-white p-3 rounded shadow-sm text-xs border">
                        <div>
                            <span class="font-bold text-gray-800">{{ $handoff->customer->full_name ?? ($handoff->customer->first_name . ' ' . $handoff->customer->last_name) }}</span>
                            <span class="text-gray-500 ml-2">Plan: {{ $handoff->package->name ?? 'N/A' }}</span>
                        </div>
                        <form action="{{ route('admin.subscribers.activate-handoff', $handoff) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded font-bold hover:bg-green-700">
                                Activate Service Account
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Account Number</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Plan / Package</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Monthly Fee</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Activated At</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($subscribers as $sub)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-blue-600">
                            <a href="{{ route('admin.subscribers.show', $sub) }}">{{ $sub->account_number }}</a>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-800">
                            {{ $sub->customer->full_name ?? ($sub->customer->first_name . ' ' . $sub->customer->last_name) }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $sub->currentSubscription->package_name_snapshot ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 font-mono font-bold">
                            PHP {{ number_format($sub->currentSubscription->monthly_price_snapshot ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">
                                {{ $sub->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500">
                            {{ $sub->activated_at ? $sub->activated_at->format('Y-m-d') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-indigo-600 hover:text-indigo-900 font-medium">
                            <a href="{{ route('admin.subscribers.show', $sub) }}">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No active subscribers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">
            {{ $subscribers->links() }}
        </div>
    </div>
</div>
@endsection
