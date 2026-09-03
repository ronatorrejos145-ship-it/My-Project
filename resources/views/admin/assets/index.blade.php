@extends('layouts.app')

@section('title', 'Assets - Master Data')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Equipment Assets</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Track company and customer hardware premises equipment.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.assets.tools') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-medium text-sm transition">
                View Technical Tools
            </a>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <form method="GET" action="{{ route('admin.assets.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tag, serial, or MAC..." class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white w-full max-w-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                        <th class="p-4">Asset Tag</th>
                        <th class="p-4">Category / Model</th>
                        <th class="p-4">Serial / MAC</th>
                        <th class="p-4">Purchase Cost</th>
                        <th class="p-4">Current Location</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-medium text-indigo-600 dark:text-indigo-400">{{ $asset->asset_tag }}</td>
                            <td class="p-4">
                                <div class="font-semibold text-slate-800 dark:text-white">{{ $asset->category->name ?? '—' }}</div>
                                <div class="text-xs text-slate-400">{{ $asset->model->model_name ?? '' }}</div>
                            </td>
                            <td class="p-4 font-mono text-xs">
                                <div>SN: {{ $asset->serial_number ?? '—' }}</div>
                                <div class="text-slate-400">MAC: {{ $asset->mac_address ?? '—' }}</div>
                            </td>
                            <td class="p-4 font-mono font-medium">₱{{ number_format($asset->purchase_cost, 2) }}</td>
                            <td class="p-4">{{ $asset->current_location ?? 'Warehouse' }}</td>
                            <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">{{ $asset->current_status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 dark:text-slate-400">No assets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $assets->links() }}
        </div>
    </div>
</div>
@endsection
