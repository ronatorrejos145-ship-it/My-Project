@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Support Ticket Queue</h1>
            <p class="text-muted small">Manage subscriber incidents, service requests, and technical helpdesk cases</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTicketModal">
            <i class="bi bi-plus-circle me-1"></i> Create Ticket
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.support.tickets.index') }}" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search ticket #, subject, or customer..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- All Statuses --</option>
                        <option value="NEW" {{ request('status') == 'NEW' ? 'selected' : '' }}>NEW</option>
                        <option value="OPEN" {{ request('status') == 'OPEN' ? 'selected' : '' }}>OPEN</option>
                        <option value="IN_PROGRESS" {{ request('status') == 'IN_PROGRESS' ? 'selected' : '' }}>IN_PROGRESS</option>
                        <option value="WAITING_CUSTOMER" {{ request('status') == 'WAITING_CUSTOMER' ? 'selected' : '' }}>WAITING CUSTOMER</option>
                        <option value="RESOLVED" {{ request('status') == 'RESOLVED' ? 'selected' : '' }}>RESOLVED</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select">
                        <option value="">-- All Priorities --</option>
                        <option value="LOW" {{ request('priority') == 'LOW' ? 'selected' : '' }}>LOW</option>
                        <option value="NORMAL" {{ request('priority') == 'NORMAL' ? 'selected' : '' }}>NORMAL</option>
                        <option value="HIGH" {{ request('priority') == 'HIGH' ? 'selected' : '' }}>HIGH</option>
                        <option value="URGENT" {{ request('priority') == 'URGENT' ? 'selected' : '' }}>URGENT</option>
                        <option value="CRITICAL" {{ request('priority') == 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
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
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>Resolution Due</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $tkt)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.support.tickets.show', $tkt) }}" class="fw-bold text-decoration-none">
                                        {{ $tkt->ticket_number }}
                                    </a>
                                </td>
                                <td>{{ $tkt->customer->full_name }}</td>
                                <td>{{ $tkt->subject }}</td>
                                <td><span class="badge bg-info text-dark">{{ $tkt->category }}</span></td>
                                <td><span class="badge bg-secondary">{{ $tkt->priority }}</span></td>
                                <td>{{ $tkt->assignedUser->name ?? 'Unassigned' }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $tkt->status }}</span></td>
                                <td>
                                    <small class="{{ $tkt->is_sla_breached ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ $tkt->resolution_due_at ? $tkt->resolution_due_at->format('M d, Y H:i') : 'N/A' }}
                                    </small>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.support.tickets.show', $tkt) }}" class="btn btn-sm btn-outline-primary">
                                        Open Workspace
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No tickets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="createTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('admin.support.tickets.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Support Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Subscriber <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Select Subscriber --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->customer_number }} - {{ $c->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" required placeholder="e.g. Slow internet connection in Barangay 1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            <option value="LOW">LOW</option>
                            <option value="NORMAL" selected>NORMAL</option>
                            <option value="HIGH">HIGH</option>
                            <option value="URGENT">URGENT</option>
                            <option value="CRITICAL">CRITICAL</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="TECHNICAL">TECHNICAL (Speed / Loss / WiFi)</option>
                            <option value="BILLING">BILLING & INVOICING</option>
                            <option value="PAYMENT">PAYMENT VERIFICATION</option>
                            <option value="INSTALLATION">INSTALLATION / RELOCATION</option>
                            <option value="ACCOUNT">ACCOUNT MANAGEMENT</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Issue Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" required placeholder="Describe subscriber issue in detail..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Ticket</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
