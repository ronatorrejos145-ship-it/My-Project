@extends('layouts.app')

@section('title', 'Technical Survey #' . $survey->survey_number)

@section('content')
<div class="p-6">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Survey #{{ $survey->survey_number }}</h1>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                        {{ $survey->status }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">
                        {{ $survey->priority }} PRIORITY
                    </span>
                </div>
                <div class="mt-2 flex flex-wrap gap-4 text-xs font-mono text-slate-500">
                    <span>Applicant: <strong>{{ $survey->application->applicant_name ?? $survey->customer->full_name }}</strong></span>
                    <span>Requested Plan: <strong>{{ $survey->package->name }}</strong></span>
                    <span>Technician: <strong>{{ $survey->technician->user->name ?? 'Unassigned' }}</strong></span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.technical-surveys.report', $survey) }}" target="_blank" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-bold">
                    📄 Download PDF Report
                </a>
                <a href="{{ route('admin.technical-surveys.review.form', $survey) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold">
                    Supervisor Approval Portal ↗
                </a>
            </div>
        </div>

        <!-- GPS Arrival Verification Banner -->
        <div class="mt-4 p-4 rounded-xl border flex flex-col sm:flex-row justify-between items-center gap-3 {{ $survey->arrival_verification_status === 'ARRIVED_AT_SITE' ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-amber-50 border-amber-300 text-amber-900' }}">
            <div>
                <span class="font-bold text-sm">GPS Arrival Verification: {{ $survey->arrival_verification_status }}</span>
                <div class="text-xs font-mono mt-0.5">Technician distance from customer location: {{ $survey->arrival_distance_meters ?? 'Not verified' }} meters</div>
            </div>

            <form method="POST" action="{{ route('admin.technical-surveys.verify-gps', $survey) }}" class="flex gap-2">
                @csrf
                <input type="hidden" name="latitude" value="{{ $survey->application?->latitude ?: 14.6520000 }}">
                <input type="hidden" name="longitude" value="{{ $survey->application?->longitude ?: 121.0320000 }}">
                <button type="submit" class="px-4 py-1.5 bg-emerald-700 text-white rounded-lg text-xs font-bold shadow">
                    📍 Verify On-Site GPS Arrival
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Survey Form Column -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Technician Inspection & Recommendation Form -->
            <form method="POST" action="{{ route('admin.technical-surveys.submit', $survey) }}" class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 space-y-4">
                @csrf
                <h3 class="font-bold text-slate-800 dark:text-white text-base pb-3 border-b border-slate-200 dark:border-slate-800">📋 Field Technical Assessment</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Line of Sight Status *</label>
                        <select name="line_of_sight_status" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-semibold">
                            <option value="CLEAR" {{ $survey->line_of_sight_status === 'CLEAR' ? 'selected' : '' }}>CLEAR (100% Unobstructed)</option>
                            <option value="PARTIAL" {{ $survey->line_of_sight_status === 'PARTIAL' ? 'selected' : '' }}>PARTIAL (Minor Obstruction)</option>
                            <option value="BLOCKED" {{ $survey->line_of_sight_status === 'BLOCKED' ? 'selected' : '' }}>BLOCKED (Obstructed)</option>
                            <option value="NOT_APPLICABLE" {{ $survey->line_of_sight_status === 'NOT_APPLICABLE' ? 'selected' : '' }}>NOT_APPLICABLE (FTTH/Fiber)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Installation Complexity *</label>
                        <select name="installation_complexity" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-semibold">
                            <option value="EASY">EASY</option>
                            <option value="NORMAL" selected>NORMAL</option>
                            <option value="MODERATE">MODERATE</option>
                            <option value="DIFFICULT">DIFFICULT</option>
                            <option value="VERY_DIFFICULT">VERY_DIFFICULT</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Site Safety Assessment *</label>
                        <select name="safety_assessment" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-semibold">
                            <option value="SAFE" selected>SAFE</option>
                            <option value="CAUTION">CAUTION</option>
                            <option value="UNSAFE">UNSAFE</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Technical Findings & Executive Summary *</label>
                    <textarea name="technical_summary" rows="3" required placeholder="Describe site access, electrical availability, roof mounting, line of sight, and fiber drop path..." class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs">{{ old('technical_summary', $survey->technical_summary) }}</textarea>
                </div>

                <div class="pt-3 border-t flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs shadow">
                        Submit Field Assessment for Review 🚀
                    </button>
                </div>
            </form>

            <!-- Site Photos Gallery & Uploader -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-slate-800 dark:text-white text-base">📷 Field Site Photos</h3>
                    <button onclick="document.getElementById('photoModal').classList.remove('hidden')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold">
                        + Upload Photo
                    </button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @forelse($survey->photos as $p)
                        <div class="p-2 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 text-xs">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">{{ $p->category }}</span>
                            <div class="mt-1 font-semibold truncate">{{ $p->original_filename }}</div>
                            <div class="text-[10px] text-slate-400 mt-1">{{ round($p->file_size / 1024, 1) }} KB</div>
                        </div>
                    @empty
                        <p class="col-span-3 text-xs text-slate-400 py-3">No field photos uploaded yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Sidebar Column -->
        <div class="space-y-6">
            <!-- Signal Measurements -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-800 dark:text-white text-base pb-3 border-b border-slate-200 dark:border-slate-800">📊 Field Signal Measurements</h3>
                <div class="mt-3 space-y-2 text-xs font-mono">
                    @forelse($survey->measurements as $m)
                        <div class="p-2 bg-slate-50 dark:bg-slate-800 rounded border border-slate-200 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-slate-800 dark:text-white">{{ $m->measurement_type }}</span>
                                <div class="text-[10px] text-slate-400">{{ $m->value }} {{ $m->unit }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">{{ $m->acceptance_status }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-2">No signal measurements logged.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Upload Photo -->
<div id="photoModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-md w-full p-6 border">
        <h3 class="text-lg font-bold">Upload Field Site Photo</h3>
        <form method="POST" action="{{ route('admin.technical-surveys.upload-photo', $survey) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold">Photo Category *</label>
                <select name="category" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm">
                    <option value="FACADE">FACADE (Building Exterior)</option>
                    <option value="MOUNTING_LOCATION">MOUNTING_LOCATION (Roof/Wall)</option>
                    <option value="CABLE_ROUTE">CABLE_ROUTE</option>
                    <option value="LINE_OF_SIGHT">LINE_OF_SIGHT</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold">Image File (JPG, PNG) *</label>
                <input type="file" name="photo" required class="mt-1 w-full text-xs">
            </div>

            <div class="flex justify-end gap-2 pt-3">
                <button type="button" onclick="document.getElementById('photoModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg text-xs">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold">Upload Photo</button>
            </div>
        </form>
    </div>
</div>
@endsection
