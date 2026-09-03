@extends('layouts.app')

@section('title', 'Import GPS Coordinates - GIS')

@section('content')
<div class="p-6 max-w-4xl">
    <div class="pb-6 border-b border-slate-200 dark:border-slate-800">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Bulk GPS Coordinates Import</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Import bulk infrastructure and subscriber GPS coordinates via CSV file.</p>
    </div>

    @if(session('success'))
        <div class="mt-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.gis.import.csv') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="font-bold text-slate-800 dark:text-white text-sm">CSV File Specification</h3>
            <p class="text-xs text-slate-500">Header format: <code>entity_type, code, name, latitude, longitude</code></p>
            <p class="text-xs text-slate-500">Entity Types supported: <code>NODE</code>, <code>AP</code>, <code>TOWER</code>, <code>DP</code></p>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Select CSV File *</label>
                <input type="file" name="gis_file" required accept=".csv,.txt" class="mt-2 w-full text-xs">
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm">
                Upload & Process CSV
            </button>
        </div>
    </form>

    <!-- Import Audit Logs Table -->
    <div class="mt-8 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm">
            Recent GIS Coordinate Imports
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 border-b border-slate-200 dark:border-slate-800 font-semibold">
                        <th class="p-4">Filename</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Processed</th>
                        <th class="p-4">Imported</th>
                        <th class="p-4">Failed</th>
                        <th class="p-4">Imported By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($imports as $imp)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-bold text-slate-800 dark:text-white">{{ $imp->original_filename }}</td>
                            <td class="p-4"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-800 font-bold">{{ $imp->file_type }}</span></td>
                            <td class="p-4 font-mono text-xs">{{ $imp->records_processed }}</td>
                            <td class="p-4 font-mono text-xs font-bold text-emerald-600">{{ $imp->records_imported }}</td>
                            <td class="p-4 font-mono text-xs font-bold text-rose-600">{{ $imp->records_failed }}</td>
                            <td class="p-4 text-xs">{{ $imp->importer->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">No GIS coordinate imports recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
