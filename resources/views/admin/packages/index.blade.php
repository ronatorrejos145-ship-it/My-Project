@extends('layouts.app')

@section('title', 'Service Packages - Master Data')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Service Packages & Plans</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Residential, business, and corporate broadband plans with version pricing.</p>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <form method="GET" action="{{ route('admin.packages.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search package code or name..." class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white w-full max-w-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                        <th class="p-4">Package Code</th>
                        <th class="p-4">Package Name</th>
                        <th class="p-4">Speeds (DL / UL)</th>
                        <th class="p-4">Base Price</th>
                        <th class="p-4">Billing Cycle</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($packages as $pkg)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-medium text-indigo-600 dark:text-indigo-400">{{ $pkg->package_code }}</td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-white">{{ $pkg->name }}</td>
                            <td class="p-4 font-mono text-xs"><span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-bold">⚡ {{ $pkg->download_speed }} / {{ $pkg->upload_speed }} {{ $pkg->speed_unit }}</span></td>
                            <td class="p-4 font-mono font-bold text-slate-800 dark:text-white">₱{{ number_format($pkg->base_price, 2) }}</td>
                            <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs bg-slate-100 dark:bg-slate-800">{{ $pkg->billingCycle->name ?? 'Monthly' }}</span></td>
                            <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">{{ $pkg->status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 dark:text-slate-400">No service packages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $packages->links() }}
        </div>
    </div>
</div>
@endsection
