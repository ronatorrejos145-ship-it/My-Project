@extends('layouts.app')

@section('title', 'Chart of Accounts - Finance')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Chart of Accounts</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Financial accounting structure for double-entry ledger integration.</p>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <form method="GET" action="{{ route('admin.finance.accounts.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search account code or name..." class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white w-full max-w-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                        <th class="p-4">Account Code</th>
                        <th class="p-4">Account Name</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Normal Balance</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($accounts as $acc)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $acc->account_code }}</td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-white">{{ $acc->account_name }}</td>
                            <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">{{ $acc->account_type }}</span></td>
                            <td class="p-4 font-mono text-xs">{{ $acc->normal_balance }}</td>
                            <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">{{ $acc->status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500 dark:text-slate-400">No chart of accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $accounts->links() }}
        </div>
    </div>
</div>
@endsection
