@extends('layouts.app')

@section('title', 'Application #' . $application->application_number)

@section('content')
<div class="p-6">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $application->applicant_name }}</h1>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                        {{ $application->status }}
                    </span>
                </div>
                <div class="mt-2 flex flex-wrap gap-4 text-xs font-mono text-slate-500">
                    <span>Application #: <strong class="text-indigo-600 dark:text-indigo-400">{{ $application->application_number }}</strong></span>
                    <span>Requested Plan: <strong>{{ $application->package->name ?? '—' }}</strong></span>
                    <span>Phone: <strong>{{ $application->primary_phone }}</strong></span>
                    <span>Submitted: <strong>{{ $application->submitted_at?->format('M d, Y H:i A') }}</strong></span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button onclick="document.getElementById('statusModal').classList.remove('hidden')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold">
                    Change Status
                </button>
                @if($application->latestServiceabilityCheck)
                    <button onclick="document.getElementById('overrideModal').classList.remove('hidden')" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold">
                        Supervisor Override
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Column: Serviceability & Map -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Serviceability Check Evaluation -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-800 dark:text-white text-base pb-3 border-b border-slate-200 dark:border-slate-800">📡 Technical Serviceability Engine Outcome</h3>

                @if($check = $application->latestServiceabilityCheck)
                    <div class="mt-4 p-4 rounded-xl border {{ $check->result_status === 'SERVICEABLE' ? 'bg-emerald-50 border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-800' : 'bg-amber-50 border-amber-200 dark:bg-amber-950/40 dark:border-amber-800' }}">
                        <div class="flex justify-between items-center">
                            <span class="px-3 py-1 rounded-full text-xs font-black tracking-wider {{ $check->result_status === 'SERVICEABLE' ? 'bg-emerald-200 text-emerald-900' : 'bg-amber-200 text-amber-900' }}">
                                {{ $check->result_status }}
                            </span>
                            <span class="text-xs font-mono text-slate-500">Reason Code: <strong>{{ $check->reason_code }}</strong></span>
                        </div>

                        <p class="mt-3 text-sm text-slate-800 dark:text-slate-200 font-medium">{{ $check->explanation }}</p>

                        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2 font-mono text-xs text-slate-600 dark:text-slate-400 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <div>Distance: <strong>{{ $check->calculated_distance_meters ?? 'N/A' }} meters</strong></div>
                            <div>Nearest Node: <strong>{{ $check->nearestNode->name ?? 'None' }}</strong></div>
                            <div>Capacity: <strong>{{ $check->capacity_status }}</strong></div>
                        </div>

                        @if($check->is_overridden)
                            <div class="mt-3 p-3 bg-purple-100 border border-purple-300 text-purple-900 rounded-lg text-xs font-semibold">
                                ⚠️ Supervisor Override Applied: {{ $check->override_result_status }}
                                <div class="font-normal text-[11px] mt-0.5">Reason: {{ $check->override_reason }}</div>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-slate-400 py-4">No technical serviceability check recorded.</p>
                @endif
            </div>

            <!-- Map View -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-800 dark:text-white text-base pb-3 border-b border-slate-200 dark:border-slate-800">📍 Installation GPS Coordinates</h3>
                <div id="map" class="mt-3 h-64 w-full rounded-xl border border-slate-300 dark:border-slate-700 z-10"></div>
            </div>

        </div>

        <!-- Sidebar Column -->
        <div class="space-y-6">
            <!-- Applicant Details -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-800 dark:text-white text-base pb-3 border-b border-slate-200 dark:border-slate-800">📋 Installation Address</h3>
                <p class="mt-3 text-xs text-slate-700 dark:text-slate-300 font-medium">{{ $application->installationAddress->address_line_1 ?? 'Address recorded on application' }}</p>
                <div class="mt-3 font-mono text-[11px] text-slate-400">
                    GPS: {{ $application->latitude }}, {{ $application->longitude }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet Map Script -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var lat = {{ $application->latitude ?? 14.6520000 }};
        var lng = {{ $application->longitude ?? 121.0320000 }};

        var map = L.map('map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        L.marker([lat, lng]).addTo(map).bindPopup('<b>Applicant Pin</b><br>{{ $application->applicant_name }}').openPopup();

        @if($check && $check->nearestNode && $check->nearestNode->latitude)
            var nodeLat = {{ $check->nearestNode->latitude }};
            var nodeLng = {{ $check->nearestNode->longitude }};
            L.marker([nodeLat, nodeLng], { icon: L.divIcon({ className: 'bg-indigo-600 text-white p-1 rounded font-mono text-[10px]', html: 'NODE' }) }).addTo(map).bindPopup('Nearest Node: {{ $check->nearestNode->name }}');
            L.polyline([[lat, lng], [nodeLat, nodeLng]], { color: 'indigo', weight: 2, dashArray: '5, 5' }).addTo(map);
        @endif
    });
</script>

<!-- Modal: Status -->
<div id="statusModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-md w-full p-6 border">
        <h3 class="text-lg font-bold">Update Application Status</h3>
        <form method="POST" action="{{ route('admin.applications.status', $application) }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold">New Status *</label>
                <select name="status" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm">
                    <option value="UNDER_REVIEW">UNDER_REVIEW</option>
                    <option value="REQUIRES_SURVEY">REQUIRES_SURVEY</option>
                    <option value="APPROVED">APPROVED</option>
                    <option value="REJECTED">REJECTED</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold">Reason *</label>
                <input type="text" name="reason" required class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-3">
                <button type="button" onclick="document.getElementById('statusModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg text-xs">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold">Save Status</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Override -->
@if($check = $application->latestServiceabilityCheck)
<div id="overrideModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-md w-full p-6 border">
        <h3 class="text-lg font-bold">Supervisor Serviceability Override</h3>
        <form method="POST" action="{{ route('admin.serviceability.override', $check) }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold">Override Status *</label>
                <select name="override_status" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm">
                    <option value="SERVICEABLE">SERVICEABLE</option>
                    <option value="REQUIRES_TECHNICAL_SURVEY">REQUIRES_TECHNICAL_SURVEY</option>
                    <option value="OUT_OF_COVERAGE">OUT_OF_COVERAGE</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold">Reason for Override *</label>
                <input type="text" name="override_reason" required placeholder="e.g. Nearby POP expansion planned, Line of sight confirmed" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-3">
                <button type="button" onclick="document.getElementById('overrideModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg text-xs">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg text-xs font-semibold">Submit Override</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
