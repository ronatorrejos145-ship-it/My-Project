@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.support.tickets.index') }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Queue</a>
            <h1 class="h3 mb-0 text-gray-800 mt-1">Ticket {{ $ticket->ticket_number }}</h1>
            <span class="text-muted small">{{ $ticket->subject }}</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                <i class="bi bi-arrow-repeat me-1"></i> Update Status
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-md-4">
            <!-- Customer 360 Support Sidebar -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-gray-800">Subscriber 360</h5>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold mb-1">{{ $ticket->customer->full_name }}</h5>
                    <p class="text-muted small mb-2">Account #: {{ $ticket->customer->customer_number }}</p>
                    <p class="text-muted small mb-3"><i class="bi bi-telephone me-1"></i> {{ $ticket->customer->primary_phone }}</p>
                    <hr>
                    <div class="mb-2"><strong>Category:</strong> <span class="badge bg-info text-dark">{{ $ticket->category }}</span></div>
                    <div class="mb-2"><strong>Priority:</strong> <span class="badge bg-secondary">{{ $ticket->priority }}</span></div>
                    <div class="mb-2"><strong>Status:</strong> <span class="badge bg-warning text-dark">{{ $ticket->status }}</span></div>
                    <div class="mb-2"><strong>First Response Due:</strong> <small class="text-muted">{{ $ticket->first_response_due_at ? $ticket->first_response_due_at->format('M d, Y H:i') : 'N/A' }}</small></div>
                    <div class="mb-0"><strong>Resolution Due:</strong> <small class="{{ $ticket->is_sla_breached ? 'text-danger fw-bold' : 'text-muted' }}">{{ $ticket->resolution_due_at ? $ticket->resolution_due_at->format('M d, Y H:i') : 'N/A' }}</small></div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Ticket Conversation Workspace -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-gray-800">Conversation Thread</h5>
                </div>
                <div class="card-body">
                    @foreach($ticket->messages as $msg)
                        <div class="p-3 mb-3 rounded {{ $msg->visibility === 'INTERNAL_ONLY' ? 'bg-warning-subtle border border-warning' : 'bg-light' }}">
                            <div class="d-flex justify-content-between mb-1">
                                <strong class="{{ $msg->author_type === 'CUSTOMER' ? 'text-primary' : 'text-dark' }}">
                                    {{ $msg->user->name ?? $msg->author_type }}
                                    @if($msg->visibility === 'INTERNAL_ONLY')
                                        <span class="badge bg-warning text-dark ms-1"><i class="bi bi-lock me-1"></i> INTERNAL NOTE</span>
                                    @endif
                                </strong>
                                <small class="text-muted">{{ $msg->created_at->format('M d, Y H:i') }}</small>
                            </div>
                            <p class="mb-0 text-break">{{ $msg->message }}</p>
                        </div>
                    @endforeach

                    <hr class="my-4">

                    <form method="POST" action="{{ route('admin.support.tickets.reply', $ticket) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Agent Reply / Note</label>
                            <textarea name="message" class="form-control" rows="3" required placeholder="Type your response..."></textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="visibility" id="visPublic" value="CUSTOMER_VISIBLE" checked>
                                    <label class="form-check-label" for="visPublic">Customer Visible</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="visibility" id="visInternal" value="INTERNAL_ONLY">
                                    <label class="form-check-label text-warning fw-bold" for="visInternal"><i class="bi bi-lock"></i> Internal Staff Note Only</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Submit Reply</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.support.tickets.status', $ticket) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Ticket Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-12">
                        <label class="form-label">New Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="OPEN">OPEN</option>
                            <option value="IN_PROGRESS">IN_PROGRESS</option>
                            <option value="WAITING_CUSTOMER">WAITING_CUSTOMER</option>
                            <option value="WAITING_INTERNAL">WAITING_INTERNAL</option>
                            <option value="RESOLVED">RESOLVED</option>
                            <option value="CLOSED">CLOSED</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Reason / Notes</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Status change explanation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
