@extends('layouts.app')

@section('title', 'New Version - ' . $package->name)

@section('content')
<div class="p-6 max-w-3xl">
    <div class="pb-6 border-b border-slate-200 dark:border-slate-800">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Publish New Version for {{ $package->name }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Schedule price or speed updates while maintaining historical billing integrity.</p>
    </div>

    <form method="POST" action="{{ route('admin.packages.versions.store', $package) }}" class="mt-6 space-y-6">
        @csrf

        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Version Name / Title *</label>
                    <input type="text" name="version_name" value="{{ old('version_name', 'Q3 Price Revision') }}" required class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Effective Start Date *</label>
                    <input type="date" name="effective_from" value="{{ old('effective_from', date('Y-m-d')) }}" required class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">New Monthly Price (₱) *</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $latestVersion?->price ?? $package->base_price) }}" required class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Installation Fee (₱)</label>
                    <input type="number" step="0.01" name="installation_fee" value="{{ old('installation_fee', $latestVersion?->installation_fee ?? 1500.00) }}" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Download Speed (Mbps) *</label>
                    <input type="number" name="download_speed" value="{{ old('download_speed', $latestVersion?->download_speed ?? $package->download_speed) }}" required class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Upload Speed (Mbps) *</label>
                    <input type="number" name="upload_speed" value="{{ old('upload_speed', $latestVersion?->upload_speed ?? $package->upload_speed) }}" required class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Reason for Change / Audit Note *</label>
                <textarea name="change_reason" rows="2" required placeholder="e.g. Speed upgrade for existing customers, annual price adjustment" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm"></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <a href="{{ route('admin.packages.show', $package) }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Publish New Version</button>
        </div>
    </form>
</div>
@endsection
