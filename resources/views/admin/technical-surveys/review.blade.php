@extends('layouts.app')

@section('title', 'Supervisor Review - Survey #' . $survey->survey_number)

@section('content')
<div class="p-6 max-w-4xl">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <div class="pb-6 border-b border-slate-200 dark:border-slate-800">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Supervisor Technical Approval Portal</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Review field survey evidence for Survey #{{ $survey->survey_number }}</p>
        </div>

        <div class="mt-6 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border space-y-3 text-xs">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 font-mono">
                <div>Applicant: <strong>{{ $survey->application->applicant_name ?? $survey->customer->full_name }}</strong></div>
                <div>Plan: <strong>{{ $survey->package->name }}</strong></div>
                <div>Technician: <strong>{{ $survey->technician->user->name ?? 'Unassigned' }}</strong></div>
                <div>Complexity: <strong>{{ $survey->installation_complexity }}</strong></div>
            </div>

            <div class="pt-3 border-t">
                <strong>Field Technical Findings:</strong>
                <p class="mt-1 text-slate-700 dark:text-slate-300 font-medium">{{ $survey->technical_summary }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.technical-surveys.review', $survey) }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Supervisor Decision *</label>
                <select name="decision" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-bold">
                    <option value="APPROVED">APPROVE — Technically Feasible (Handoff to Installation)</option>
                    <option value="REJECTED">REJECT — Technically Not Feasible</option>
                    <option value="REQUEST_RESURVEY">REQUEST_RESURVEY — Request Secondary On-Site Inspection</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Decision Justification / Reason *</label>
                <textarea name="reason" rows="2" required placeholder="Provide technical justification for approval or rejection..." class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('admin.technical-surveys.show', $survey) }}" class="px-4 py-2 border rounded-lg text-xs">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow">
                    Save Decision & Update Handoff
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
