@extends('layouts.app')

@section('title', 'Service Packages - Product Catalog')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Service Packages & Internet Plans</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Master product catalog of residential, business, and corporate broadband offerings.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.packages.categories.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-medium text-sm transition">
                Categories
            </a>
            <a href="{{ route('admin.packages.promotions.index') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium text-sm transition">
                Promotions
            </a>
            <a href="{{ route('admin.packages.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium text-sm transition">
                + Add Plan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row gap-2 justify-between">
            <form method="GET" action="{{ route('admin.packages.index') }}" class="flex flex-wrap gap-2 w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search plan code or name..." class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white w-full sm:w-64">

                <select name="category_id" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select name="status" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white">
                    <option value="">All Statuses</option>
                    <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                    <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>INACTIVE</option>
                    <option value="DISCONTINUED" {{ request('status') === 'DISCONTINUED' ? 'selected' : '' }}>DISCONTINUED</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                        <th class="p-4">Package Code</th>
                        <th class="p-4">Plan Name</th>
                        <th class="p-4">Category / Type</th>
                        <th class="p-4">Speeds (DL / UL)</th>
                        <th class="p-4">Monthly Price</th>
                        <th class="p-4">Active Version</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($packages as $pkg)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $pkg->package_code }}</td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-white">
                                <a href="{{ route('admin.packages.show', $pkg) }}" class="hover:underline hover:text-indigo-600">
                                    {{ $pkg->name }}
                                </a>
                            </td>
                            <td class="p-4">
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ $pkg->category->name ?? '—' }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $pkg->package_type }} • {{ $pkg->technology }}</div>
                            </td>
                            <td class="p-4 font-mono text-xs">
                                <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-bold">⚡ {{ $pkg->download_speed_formatted }} / {{ $pkg->upload_speed_formatted }}</span>
                            </td>
                            <td class="p-4 font-mono font-bold text-slate-800 dark:text-white">₱{{ number_format($pkg->base_price, 2) }}</td>
                            <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800">v{{ $pkg->activeVersion->version_number ?? 1 }}</span></td>
                            <td class="p-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $pkg->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $pkg->status }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('admin.packages.show', $pkg) }}" class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-300 rounded font-medium text-xs hover:bg-indigo-100">
                                    Manage Plan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500 dark:text-slate-400">No service packages found in product catalog.</td>
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
