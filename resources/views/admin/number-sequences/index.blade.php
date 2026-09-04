@extends('layouts.app')

@section('title', 'Number Sequences - System Settings')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Centralized Number Sequences</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Concurrency-safe document and record identifier numbering rules.</p>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <form method="GET" action="{{ route('admin.number-sequences.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search sequence code or name..." class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white w-full max-w-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                        <th class="p-4">Code</th>
                        <th class="p-4">Sequence Name</th>
                        <th class="p-4">Prefix</th>
                        <th class="p-4">Current Number</th>
                        <th class="p-4">Padding</th>
                        <th class="p-4">Reset Period</th>
                        <th class="p-4">Branch Aware</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($sequences as $seq)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-medium text-indigo-600 dark:text-indigo-400">{{ $seq->code }}</td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-white">{{ $seq->name }}</td>
                            <td class="p-4 font-mono text-xs font-bold text-slate-700 dark:text-slate-200">{{ $seq->prefix }}</td>
                            <td class="p-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $seq->current_number }}</td>
                            <td class="p-4 font-mono text-xs">{{ $seq->padding }} digits</td>
                            <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs bg-slate-100 dark:bg-slate-800">{{ $seq->reset_period }}</span></td>
                            <td class="p-4"><span class="px-2 py-0.5 rounded text-xs font-bold {{ $seq->is_branch_aware ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-600' }}">{{ $seq->is_branch_aware ? 'Yes' : 'No' }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 dark:text-slate-400">No number sequences found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $sequences->links() }}
        </div>
    </div>
</div>
@endsection
