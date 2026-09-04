@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Customer Support & SLA Command Center</h1>
            <p class="text-muted small">Monitor open helpdesk tickets, SLA breaches, customer complaints, and major service incidents</p>
        </div>
        <a href="{{ route('admin.support.tickets.index') }}" class="btn btn-primary">
            <i class="bi bi-headset me-1"></i> Open Support Queue
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold small">Open Support Tickets</h6>
                    <h3 class="fw-bold mb-0 text-primary">{{ $openTicketsCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold small">SLA Breached Tickets</h6>
                    <h3 class="fw-bold mb-0 text-danger">{{ $breachedCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold small">Active Major Incidents</h6>
                    <h3 class="fw-bold mb-0 text-warning">{{ $activeIncidents->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold small">Quick Links</h6>
                    <a href="{{ route('admin.support.complaints.index') }}" class="btn btn-sm btn-outline-danger d-block mb-1">Complaints Queue</a>
                    <a href="{{ route('admin.support.knowledge-base.index') }}" class="btn btn-sm btn-outline-info d-block">Knowledge Base</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-gray-800">Recent Support Queue Activity</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ticket #</th>
                            <th>Subscriber</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>SLA Deadline</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTickets as $tkt)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.support.tickets.show', $tkt) }}" class="fw-bold text-decoration-none">
                                        {{ $tkt->ticket_number }}
                                    </a>
                                </td>
                                <td>{{ $tkt->customer->full_name }}</td>
                                <td>{{ $tkt->subject }}</td>
                                <td><span class="badge bg-info text-dark">{{ $tkt->category }}</span></td>
                                <td>
                                    @if($tkt->priority === 'CRITICAL' || $tkt->priority === 'URGENT')
                                        <span class="badge bg-danger">{{ $tkt->priority }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $tkt->priority }}</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-warning text-dark">{{ $tkt->status }}</span></td>
                                <td>
                                    <small class="{{ $tkt->is_sla_breached ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ $tkt->resolution_due_at ? $tkt->resolution_due_at->format('M d, Y H:i') : 'N/A' }}
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No support tickets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
