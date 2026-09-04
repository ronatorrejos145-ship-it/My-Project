@extends('layouts.app')

@section('title', 'Promotions - Catalog')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Promotions & Discount Campaigns</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage promotional discounts, free installations, and seasonal campaigns.</p>
        </div>
        <div>
            <button onclick="document.getElementById('promoModal').classList.remove('hidden')" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium text-sm transition">
                + Add Promotion
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                        <th class="p-4">Promo Code</th>
                        <th class="p-4">Campaign Name</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Discount Value</th>
                        <th class="p-4">Validity Window</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($promotions as $promo)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-bold text-purple-600 dark:text-purple-400">{{ $promo->code }}</td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-white">{{ $promo->name }}</td>
                            <td class="p-4"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-800">{{ $promo->promo_type }}</span></td>
                            <td class="p-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">₱{{ number_format($promo->discount_amount, 2) }}</td>
                            <td class="p-4 text-xs font-mono">
                                {{ $promo->start_date?->format('M d, Y') }} → {{ $promo->end_date ? $promo->end_date->format('M d, Y') : 'Ongoing' }}
                            </td>
                            <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">{{ $promo->status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 dark:text-slate-400">No promotional campaigns found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $promotions->links() }}
        </div>
    </div>
</div>

<!-- Modal: Add Promotion -->
<div id="promoModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Create Promotion</h3>
        <form method="POST" action="{{ route('admin.packages.promotions.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Promo Code *</label>
                <input type="text" name="code" required placeholder="e.g. PROMO-SUMMER" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Campaign Name *</label>
                <input type="text" name="name" required placeholder="e.g. Summer Free Installation Promo" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Promo Type *</label>
                    <select name="promo_type" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="FREE_INSTALLATION">FREE_INSTALLATION</option>
                        <option value="DISCOUNT">DISCOUNT</option>
                        <option value="FIRST_MONTH_FREE">FIRST_MONTH_FREE</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Discount Amount (₱)</label>
                    <input type="number" step="0.01" name="discount_amount" value="1500.00" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Start Date *</label>
                <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
            </div>

            <input type="hidden" name="status" value="ACTIVE">

            <div class="flex justify-end gap-2 pt-3">
                <button type="button" onclick="document.getElementById('promoModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg text-xs">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-xs font-semibold">Save Promotion</button>
            </div>
        </form>
    </div>
</div>
@endsection
