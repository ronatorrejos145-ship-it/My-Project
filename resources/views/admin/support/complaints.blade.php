@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h1 class="h3 mb-0 text-gray-800">Customer Complaints Queue</h1>
        <p class="text-muted small">Manage subscriber dissatisfaction cases, escalation tracking, and root cause analysis</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Complaint #</th>
                            <th>Subscriber</th>
                            <th>Category</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Assigned Officer</th>
                            <th>Date Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints as $cmp)
                            <tr>
                                <td><code>{{ $cmp->complaint_number }}</code></td>
                                <td class="fw-bold">{{ $cmp->customer->full_name }}</td>
                                <td><span class="badge bg-info text-dark">{{ $cmp->category }}</span></td>
                                <td><span class="badge bg-danger">{{ $cmp->severity }}</span></td>
                                <td><span class="badge bg-warning text-dark">{{ $cmp->status }}</span></td>
                                <td>{{ $cmp->assignedOfficer->name ?? 'Unassigned' }}</td>
                                <td>{{ $cmp->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No complaints logged.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $complaints->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
