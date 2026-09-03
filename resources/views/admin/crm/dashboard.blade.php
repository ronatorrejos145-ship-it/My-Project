@extends('layouts.app')

@section('title', 'CRM & Subscriber Overview')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">CRM & Customer Dashboard</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Real-time metrics for customer lifecycle, lead conversion, and follow-ups.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.customers.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium text-sm transition">
                + New Customer
            </a>
            <a href="{{ route('admin.leads.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-medium text-sm transition">
                Manage CRM Leads
            </a>
        </div>
    </div>

    <!-- Stat Summary Cards -->
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Subscribers</span>
            <div class="mt-2 text-3xl font-black text-slate-800 dark:text-white">{{ number_format($totalCustomers) }}</div>
            <div class="mt-1 text-xs text-emerald-600 dark:text-emerald-400 font-semibold">{{ $activeCustomers }} Active Accounts</div>
        </div>

        <div class="p-5 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Prospects & Applicants</span>
            <div class="mt-2 text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ number_format($prospects) }}</div>
            <div class="mt-1 text-xs text-slate-500">Pending Verification</div>
        </div>

        <div class="p-5 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">CRM Sales Pipeline</span>
            <div class="mt-2 text-3xl font-black text-purple-600 dark:text-purple-400">{{ number_format($totalLeads) }}</div>
            <div class="mt-1 text-xs text-purple-600 font-semibold">{{ $conversionRate }}% Conversion Rate</div>
        </div>

        <div class="p-5 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Follow-ups</span>
            <div class="mt-2 text-3xl font-black text-amber-600 dark:text-amber-400">{{ number_format($overdueFollowUps) }}</div>
            <div class="mt-1 text-xs text-amber-600 font-semibold">Overdue Sales Tasks</div>
        </div>
    </div>

    <!-- Recent Activity Tables -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Customers -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 dark:text-white">Recent Customer Accounts</h3>
                <a href="{{ route('admin.customers.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">View All</a>
            </div>
            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($recentCustomers as $c)
                    <div class="p-4 flex justify-between items-center hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                        <div>
                            <a href="{{ route('admin.customers.show', $c) }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ $c->full_name }}
                            </a>
                            <div class="text-xs text-slate-400 font-mono">#{{ $c->customer_number }} • {{ $c->primary_phone }}</div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                            {{ $c->status }}
                        </span>
                    </div>
                @empty
                    <p class="p-6 text-sm text-slate-500">No customers registered yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Sales Leads -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 dark:text-white">Recent Sales Leads</h3>
                <a href="{{ route('admin.leads.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">View Pipeline</a>
            </div>
            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($recentLeads as $l)
                    <div class="p-4 flex justify-between items-center hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                        <div>
                            <a href="{{ route('admin.leads.show', $l) }}" class="font-bold text-purple-600 dark:text-purple-400 hover:underline">
                                {{ $l->name }}
                            </a>
                            <div class="text-xs text-slate-400 font-mono">#{{ $l->lead_number }} • {{ $l->source }}</div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300">
                            {{ $l->status }}
                        </span>
                    </div>
                @empty
                    <p class="p-6 text-sm text-slate-500">No sales leads recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
