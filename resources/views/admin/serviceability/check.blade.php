@extends('layouts.app')

@section('title', 'Serviceability Checker Tool')

@section('content')
<div class="p-6 max-w-4xl">
    <div class="pb-6 border-b border-slate-200 dark:border-slate-800">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Technical Serviceability Checker</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Run instant Haversine GPS calculations against network nodes and access points.</p>
    </div>

    <form method="POST" action="{{ route('admin.serviceability.check') }}" class="mt-6 space-y-6">
        @csrf

        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Select Internet Package *</label>
                    <select name="service_package_id" required class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}">{{ $pkg->name }} ({{ $pkg->technology }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Target Latitude *</label>
                    <input type="text" name="latitude" required value="14.6520000" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Target Longitude *</label>
                    <input type="text" name="longitude" required value="121.0320000" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-mono">
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold">
                ⚡ Evaluate Technical Serviceability
            </button>
        </div>
    </form>

    @if($result = session('serviceability_result'))
        <div class="mt-6 p-6 rounded-2xl border {{ $result->result_status === 'SERVICEABLE' ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-amber-50 border-amber-300 text-amber-900' }}">
            <div class="flex justify-between items-center">
                <span class="text-xl font-black">{{ $result->result_status }}</span>
                <span class="text-xs font-mono">Code: {{ $result->reason_code }}</span>
            </div>
            <p class="mt-2 text-sm font-medium">{{ $result->explanation }}</p>
            <div class="mt-3 text-xs font-mono">
                Calculated Distance: <strong>{{ $result->calculated_distance_meters }} meters</strong>
            </div>
        </div>
    @endif
</div>
@endsection
