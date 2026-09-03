@extends('layouts.app')

@section('title', 'Customer Directory - CRM')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Customer Accounts Directory</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Master database of subscribers, accounts, and contact details.</p>
        </div>
        <div>
            <a href="{{ route('admin.customers.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium text-sm transition">
                + Register Customer
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row gap-3 justify-between">
            <form method="GET" action="{{ route('admin.customers.index') }}" class="flex flex-wrap gap-2 w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer, account #, phone, email..." class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white w-full sm:w-80">

                <select name="status" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white">
                    <option value="">All Statuses</option>
                    <option value="PROSPECT" {{ request('status') === 'PROSPECT' ? 'selected' : '' }}>PROSPECT</option>
                    <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                    <option value="SUSPENDED" {{ request('status') === 'SUSPENDED' ? 'selected' : '' }}>SUSPENDED</option>
                </select>

                <select name="branch_id" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                        <th class="p-4">Customer # / Account</th>
                        <th class="p-4">Subscriber Name</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Contact</th>
                        <th class="p-4">Branch</th>
                        <th class="p-4">Tags</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($customers as $c)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-medium">
                                <div class="text-indigo-600 dark:text-indigo-400 font-bold">#{{ $c->customer_number }}</div>
                                <div class="text-xs text-slate-400">{{ $c->account_number }}</div>
                            </td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-white">
                                <a href="{{ route('admin.customers.show', $c) }}" class="hover:underline hover:text-indigo-600">
                                    {{ $c->full_name }}
                                </a>
                            </td>
                            <td class="p-4"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-800 font-medium">{{ $c->customer_type }}</span></td>
                            <td class="p-4 text-xs font-mono">
                                <div>{{ $c->primary_phone }}</div>
                                <div class="text-slate-400">{{ $c->email }}</div>
                            </td>
                            <td class="p-4">{{ $c->branch->name ?? '—' }}</td>
                            <td class="p-4">
                                @foreach($c->tags as $tag)
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold text-white" style="background-color: {{ $tag->color_code }}">{{ $tag->name }}</span>
                                @endforeach
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $c->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $c->status }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('admin.customers.show', $c) }}" class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-300 rounded font-medium text-xs hover:bg-indigo-100">
                                    Customer 360
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500 dark:text-slate-400">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection
