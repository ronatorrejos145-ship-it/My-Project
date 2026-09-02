@extends('layouts.app')

@section('title', 'Customers')
@section('header', 'Customer Accounts')

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer #, account #, phone, name..." class="px-3 py-1.5 border border-gray-300 rounded text-sm">
            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded text-sm">Filter</button>
        </form>

        @can('create', App\Models\Customer::class)
        <a href="{{ route('admin.customers.create') }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded text-sm">
            New Customer
        </a>
        @endcan
    </div>

    <div class="bg-white shadow-sm rounded border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Customer #</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Account #</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Contact Person</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Type</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">Balance</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($customers as $c)
                <tr>
                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-800">{{ $c->customer_number }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $c->account_number }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $c->contact_person }}</td>
                    <td class="px-4 py-3 text-xs font-semibold text-slate-600">{{ $c->customer_type }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ ($c->status instanceof App\Enums\CustomerStatus ? $c->status->value : $c->status) === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $c->status instanceof App\Enums\CustomerStatus ? $c->status->value : $c->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-mono font-semibold text-gray-800">
                        ₱{{ number_format($c->current_balance, 2) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        @can('update', $c)
                        <a href="{{ route('admin.customers.edit', $c) }}" class="text-sky-600 hover:text-sky-900 text-xs font-semibold">Edit</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">No customer records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $customers->links() }}
    </div>
</div>
@endsection
