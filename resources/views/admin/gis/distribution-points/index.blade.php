@extends('layouts.app')

@section('title', 'Fiber Splitters - GIS Infrastructure')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Fiber Distribution Points & Splitter Cabinets</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Fiber distribution points, 1:8 / 1:16 optical splitters, and pole enclosures.</p>
        </div>
        <div>
            <button onclick="document.getElementById('dpModal').classList.remove('hidden')" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium text-sm transition">
                + Add Fiber Splitter
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
                        <th class="p-4">DP Code</th>
                        <th class="p-4">Splitter Name</th>
                        <th class="p-4">Type / Ports</th>
                        <th class="p-4">GPS Coordinates</th>
                        <th class="p-4">Parent Node</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($dps as $dp)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-bold text-purple-600 dark:text-purple-400">{{ $dp->code }}</td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-white">{{ $dp->name }}</td>
                            <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300">{{ $dp->dp_type }} ({{ $dp->capacity }} ports)</span></td>
                            <td class="p-4 font-mono text-xs">{{ $dp->latitude }}, {{ $dp->longitude }}</td>
                            <td class="p-4">{{ $dp->parentNode->name ?? '—' }}</td>
                            <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">{{ $dp->status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 dark:text-slate-400">No distribution points recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $dps->links() }}
        </div>
    </div>
</div>

<!-- Modal: Add DP -->
<div id="dpModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-md w-full p-6 border">
        <h3 class="text-lg font-bold">Add Fiber Splitter / DP</h3>
        <form method="POST" action="{{ route('admin.gis.distribution-points.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold">Splitter Code *</label>
                <input type="text" name="code" required placeholder="e.g. DP-FIBER-02" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold">Name *</label>
                <input type="text" name="name" required placeholder="e.g. Diliman Cabinet #2 Splitter" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold">DP Type *</label>
                    <select name="dp_type" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm">
                        <option value="FIBER_SPLITTER">FIBER_SPLITTER</option>
                        <option value="CABINET">CABINET</option>
                        <option value="POLE">POLE</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold">Port Capacity</label>
                    <input type="number" name="capacity" value="16" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold">Latitude *</label>
                    <input type="text" name="latitude" required value="14.6530000" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold">Longitude *</label>
                    <input type="text" name="longitude" required value="121.0330000" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm font-mono">
                </div>
            </div>

            <input type="hidden" name="status" value="ACTIVE">

            <div class="flex justify-end gap-2 pt-3">
                <button type="button" onclick="document.getElementById('dpModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg text-xs">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-xs font-semibold">Save Splitter</button>
            </div>
        </form>
    </div>
</div>
@endsection
