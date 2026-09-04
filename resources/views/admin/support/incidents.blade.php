@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h1 class="h3 mb-0 text-gray-800">Major Support Incidents</h1>
        <p class="text-muted small">Track widespread service outages and multi-customer network incidents</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Incident #</th>
                            <th>Title</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Lead Investigator</th>
                            <th>Started At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidents as $inc)
                            <tr>
                                <td><code>{{ $inc->incident_number }}</code></td>
                                <td class="fw-bold">{{ $inc->title }}</td>
                                <td><span class="badge bg-danger">{{ $inc->severity }}</span></td>
                                <td><span class="badge bg-warning text-dark">{{ $inc->status }}</span></td>
                                <td>{{ $inc->leadInvestigator->name ?? 'N/A' }}</td>
                                <td>{{ $inc->started_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No major incidents recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $incidents->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
