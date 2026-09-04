@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Bad-Debt Write-Off Requests</h1>
            <p class="text-muted small">Submit uncollectible debt write-off requests, supervisor approvals, and ledger write-off postings</p>
        </div>
        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#requestWriteOffModal">
            <i class="bi bi-trash me-1"></i> New Write-Off Request
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Write-Off #</th>
                            <th>Subscriber</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Date Requested</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td><code>{{ $req->write_off_number }}</code></td>
                                <td class="fw-bold">{{ $req->customer->full_name }}</td>
                                <td class="fw-bold text-danger">₱{{ number_format($req->amount, 2) }}</td>
                                <td>
                                    @if($req->status === 'POSTED')
                                        <span class="badge bg-success">POSTED TO LEDGER</span>
                                    @elseif($req->status === 'PENDING_APPROVAL')
                                        <span class="badge bg-warning text-dark">PENDING APPROVAL</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $req->status }}</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $req->reason }}</small></td>
                                <td>{{ $req->created_at->format('M d, Y') }}</td>
                                <td class="text-end">
                                    @if($req->status === 'PENDING_APPROVAL')
                                        <form method="POST" action="{{ route('admin.finance.collections.writeoffs.approve', $req) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve & Post</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No write-off requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="requestWriteOffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.finance.collections.writeoffs.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Bad-Debt Write-Off Request</h5>
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
                    <div class="col-md-12">
                        <label class="form-label">Uncollectible Amount (PHP) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Write-Off Reason / Justification <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" required placeholder="Explain why debt is uncollectible..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
