@extends('layouts.app')

@section('title', 'Service Applications - Management')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Customer Service Applications</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Review online applications, serviceability check outcomes, and approval handoffs.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.serviceability.check.form') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-medium text-sm transition">
                Check Serviceability Tool
            </a>
            <a href="{{ route('public.applications.wizard') }}" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium text-sm transition">
                Online Application Form ↗
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <form method="GET" action="{{ route('admin.applications.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search application #, applicant, phone..." class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white w-full max-w-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                        <th class="p-4">App #</th>
                        <th class="p-4">Applicant Name</th>
                        <th class="p-4">Requested Plan</th>
                        <th class="p-4">Serviceability Result</th>
                        <th class="p-4">Branch</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($applications as $app)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">#{{ $app->application_number }}</td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-white">
                                <a href="{{ route('admin.applications.show', $app) }}" class="hover:underline hover:text-indigo-600">
                                    {{ $app->applicant_name }}
                                </a>
                                <div class="text-xs text-slate-400 font-mono">{{ $app->primary_phone }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ $app->package->name ?? 'Broadband Plan' }}</div>
                                <div class="text-xs font-mono text-indigo-600">₱{{ number_format($app->package->base_price ?? 0, 2) }}/mo</div>
                            </td>
                            <td class="p-4">
                                @if($app->latestServiceabilityCheck)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $app->latestServiceabilityCheck->result_status === 'SERVICEABLE' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $app->latestServiceabilityCheck->result_status }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">Pending Check</span>
                                @endif
                            </td>
                            <td class="p-4">{{ $app->branch->name ?? 'Main Branch' }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                                    {{ $app->status }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('admin.applications.show', $app) }}" class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-300 rounded font-medium text-xs hover:bg-indigo-100">
                                    Review Application
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 dark:text-slate-400">No service applications found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $applications->links() }}
        </div>
    </div>
</div>
@endsection
