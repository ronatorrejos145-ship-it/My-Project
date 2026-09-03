@extends('layouts.app')

@section('title', 'CRM Sales Pipeline - Leads')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">CRM Sales Lead Pipeline</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Track inquiries, follow-ups, and lead-to-subscriber conversions.</p>
        </div>
        <div>
            <button onclick="document.getElementById('leadModal').classList.remove('hidden')" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium text-sm transition">
                + Capture Sales Lead
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <form method="GET" action="{{ route('admin.leads.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search lead name, company, or mobile..." class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-white w-full max-w-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                        <th class="p-4">Lead #</th>
                        <th class="p-4">Lead Name / Company</th>
                        <th class="p-4">Mobile / Email</th>
                        <th class="p-4">Source</th>
                        <th class="p-4">Priority</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($leads as $l)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-bold text-purple-600 dark:text-purple-400">#{{ $l->lead_number }}</td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-white">
                                <a href="{{ route('admin.leads.show', $l) }}" class="hover:underline hover:text-purple-600">
                                    {{ $l->name }}
                                </a>
                                @if($l->company)
                                    <div class="text-xs text-slate-400">{{ $l->company }}</div>
                                @endif
                            </td>
                            <td class="p-4 font-mono text-xs">
                                <div>{{ $l->mobile }}</div>
                                <div class="text-slate-400">{{ $l->email ?? '—' }}</div>
                            </td>
                            <td class="p-4"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-800 font-medium">{{ $l->source }}</span></td>
                            <td class="p-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">{{ $l->priority }}</span></td>
                            <td class="p-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300">
                                    {{ $l->status }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('admin.leads.show', $l) }}" class="px-3 py-1 bg-purple-50 dark:bg-purple-950 text-purple-600 dark:text-purple-300 rounded font-medium text-xs hover:bg-purple-100">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 dark:text-slate-400">No CRM sales leads found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $leads->links() }}
        </div>
    </div>
</div>

<!-- Modal: Capture Lead -->
<div id="leadModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Capture Sales Lead</h3>
        <form method="POST" action="{{ route('admin.leads.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Prospect Name *</label>
                <input type="text" name="name" required class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Mobile Phone *</label>
                    <input type="text" name="mobile" required placeholder="+63 917 000 0000" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Email Address</label>
                    <input type="email" name="email" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Acquisition Source *</label>
                    <select name="source" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="WALK_IN">WALK_IN</option>
                        <option value="WEBSITE">WEBSITE</option>
                        <option value="FACEBOOK">FACEBOOK</option>
                        <option value="FIELD_SALES">FIELD_SALES</option>
                        <option value="REFERRAL">REFERRAL</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Priority</label>
                    <select name="priority" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="LOW">LOW</option>
                        <option value="MEDIUM" selected>MEDIUM</option>
                        <option value="HIGH">HIGH</option>
                        <option value="URGENT">URGENT</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Notes / Inquiry Details</label>
                <textarea name="notes" rows="2" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm"></textarea>
            </div>

            <input type="hidden" name="status" value="NEW">

            <div class="flex justify-end gap-2 pt-3">
                <button type="button" onclick="document.getElementById('leadModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg text-xs">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-xs font-semibold">Save Lead</button>
            </div>
        </form>
    </div>
</div>
@endsection
