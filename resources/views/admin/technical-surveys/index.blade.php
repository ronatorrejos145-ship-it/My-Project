@extends('layouts.app')

@section('title', 'Technical Survey Work Queue')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Field Technical Surveys Queue</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Site inspections, line-of-sight checks, signal measurements, and supervisor approvals.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <form method="GET" action="{{ route('admin.technical-surveys.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search survey #..." class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white w-full max-w-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                        <th class="p-4">Survey #</th>
                        <th class="p-4">Applicant / Customer</th>
                        <th class="p-4">Requested Plan</th>
                        <th class="p-4">Assigned Technician</th>
                        <th class="p-4">GPS Arrival</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($surveys as $sur)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">#{{ $sur->survey_number }}</td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-white">
                                <a href="{{ route('admin.technical-surveys.show', $sur) }}" class="hover:underline hover:text-indigo-600">
                                    {{ $sur->application->applicant_name ?? $sur->customer->full_name }}
                                </a>
                            </td>
                            <td class="p-4">{{ $sur->package->name ?? '—' }}</td>
                            <td class="p-4 text-xs font-semibold">{{ $sur->technician->user->name ?? 'Unassigned' }}</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-xs font-bold {{ $sur->arrival_verification_status === 'ARRIVED_AT_SITE' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $sur->arrival_verification_status }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                                    {{ $sur->status }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('admin.technical-surveys.show', $sur) }}" class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-300 rounded font-medium text-xs hover:bg-indigo-100">
                                    View Survey
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 dark:text-slate-400">No technical surveys found in queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $surveys->links() }}
        </div>
    </div>
</div>
@endsection
