@extends('layouts.app')

@section('title', 'Service Areas - Master Data')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Service Areas</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Coverage zones and serviceability definition master data.</p>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <form method="GET" action="{{ route('admin.service-areas.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search service area name or code..." class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white w-full max-w-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                        <th class="p-4">Code</th>
                        <th class="p-4">Area Name</th>
                        <th class="p-4">Branch Hub</th>
                        <th class="p-4">Serviceability</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($serviceAreas as $sa)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-medium text-indigo-600 dark:text-indigo-400">{{ $sa->code }}</td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-white">{{ $sa->name }}</td>
                            <td class="p-4">{{ $sa->branch->name ?? '—' }}</td>
                            <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">{{ $sa->serviceability_status }}</span></td>
                            <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $sa->status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500 dark:text-slate-400">No service areas found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $serviceAreas->links() }}
        </div>
    </div>
</div>
@endsection
