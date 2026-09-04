@extends('layouts.app')

@section('title', 'GIS & Location Analytics')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">GIS Location Intelligence Dashboard</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Geographic coverage analytics, mapped infrastructure counts, and subscriber density.</p>
        </div>
        <div>
            <a href="{{ route('admin.gis.map') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium text-sm transition">
                🗺️ Open GIS Operations Map
            </a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mapped Subscribers</span>
            <div class="mt-2 text-3xl font-black text-slate-800 dark:text-white">{{ number_format($mappedCustomers) }} / {{ number_format($totalCustomers) }}</div>
            <div class="mt-1 text-xs text-indigo-600 font-semibold">{{ $unmappedCustomers }} Unmapped Records</div>
        </div>

        <div class="p-5 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mapped Applications</span>
            <div class="mt-2 text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ number_format($mappedApplications) }}</div>
            <div class="mt-1 text-xs text-slate-500">With GPS Map Pin</div>
        </div>

        <div class="p-5 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Network Infrastructure</span>
            <div class="mt-2 text-3xl font-black text-purple-600 dark:text-purple-400">{{ number_format($totalNodes + $totalAPs + $totalTowers) }}</div>
            <div class="mt-1 text-xs text-purple-600 font-semibold">{{ $totalNodes }} Nodes • {{ $totalAPs }} APs • {{ $totalTowers }} Towers</div>
        </div>

        <div class="p-5 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fiber Distribution Points</span>
            <div class="mt-2 text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($totalDPs) }}</div>
            <div class="mt-1 text-xs text-emerald-600 font-semibold">Active Fiber Splitter Cabinets</div>
        </div>
    </div>
</div>
@endsection
