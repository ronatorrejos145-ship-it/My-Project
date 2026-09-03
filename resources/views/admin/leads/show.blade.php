@extends('layouts.app')

@section('title', 'Lead Details - ' . $lead->name)

@section('content')
<div class="p-6">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $lead->name }}</h1>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300">
                        {{ $lead->status }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">
                        {{ $lead->priority }} PRIORITY
                    </span>
                </div>
                <div class="mt-2 flex flex-wrap gap-4 text-xs font-mono text-slate-500">
                    <span>Lead #: <strong class="text-purple-600 dark:text-purple-400">{{ $lead->lead_number }}</strong></span>
                    <span>Source: <strong>{{ $lead->source }}</strong></span>
                    <span>Company: <strong>{{ $lead->company ?? '—' }}</strong></span>
                </div>
            </div>

            <div class="flex gap-2">
                @if($lead->status !== 'CONVERTED')
                    <form method="POST" action="{{ route('admin.leads.convert', $lead) }}">
                        @csrf
                        <button type="submit" onclick="return confirm('Convert this lead into a new customer subscriber account?')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                            ⚡ Convert to Customer
                        </button>
                    </form>
                @else
                    <a href="{{ route('admin.customers.show', $lead->convertedCustomer) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold">
                        View Converted Customer ↗
                    </a>
                @endif
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <span class="text-xs text-slate-400">Mobile Phone</span>
                <p class="font-bold text-slate-800 dark:text-white">{{ $lead->mobile }}</p>
            </div>
            <div>
                <span class="text-xs text-slate-400">Email Address</span>
                <p class="font-bold text-slate-800 dark:text-white">{{ $lead->email ?? '—' }}</p>
            </div>
            <div>
                <span class="text-xs text-slate-400">Interested Package</span>
                <p class="font-bold text-slate-800 dark:text-white">{{ $lead->interestedPackage->name ?? 'Standard Broadband' }}</p>
            </div>
        </div>
    </div>

    <!-- Follow-up Activity Stream -->
    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <h3 class="font-bold text-slate-800 dark:text-white text-base pb-4 border-b border-slate-200 dark:border-slate-800">📞 Sales Follow-up Activities</h3>

        <form method="POST" action="{{ route('admin.leads.activities.store', $lead) }}" class="mt-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Activity Type</label>
                    <select name="activity_type" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs">
                        <option value="PHONE_CALL">PHONE_CALL</option>
                        <option value="SMS">SMS</option>
                        <option value="EMAIL">EMAIL</option>
                        <option value="MEETING">MEETING</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Outcome</label>
                    <input type="text" name="outcome" placeholder="e.g. Call answered, Sent quotation" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Activity Notes *</label>
                <textarea name="notes" rows="2" required placeholder="Log details of conversation..." class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-1.5 bg-slate-800 text-white text-xs font-semibold rounded-lg">Log Follow-up Activity</button>
            </div>
        </form>

        <div class="mt-6 space-y-4">
            @forelse($lead->activities as $act)
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-800 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-purple-600 dark:text-purple-400">{{ $act->activity_type }}</span>
                        <span class="text-slate-400 font-mono">{{ $act->completed_at?->format('M d, Y H:i A') }}</span>
                    </div>
                    <p class="mt-2 text-slate-700 dark:text-slate-300">{{ $act->notes }}</p>
                    @if($act->outcome)
                        <div class="mt-2 text-emerald-600 dark:text-emerald-400 font-semibold">Outcome: {{ $act->outcome }}</div>
                    @endif
                </div>
            @empty
                <p class="text-xs text-slate-400 py-4">No follow-up activities logged yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
