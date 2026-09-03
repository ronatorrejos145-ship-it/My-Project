@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Refund Requests Queue & Processing</h1>
            <p class="text-muted small">Review customer refund requests, approve payouts, and post refund reversals</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRefundModal">
            <i class="bi bi-arrow-counterclockwise me-1"></i> New Refund Request
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
                            <th>Refund #</th>
                            <th>Subscriber</th>
                            <th>Payment #</th>
                            <th>Requested Amount</th>
                            <th>Refund Type</th>
                            <th>Approval Status</th>
                            <th>Processing Status</th>
                            <th>Reason</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($refunds as $ref)
                            <tr>
                                <td><code>{{ $ref->refund_number }}</code></td>
                                <td class="fw-bold">{{ $ref->customer->full_name }}</td>
                                <td><code>{{ $ref->payment->payment_number ?? 'N/A' }}</code></td>
                                <td class="fw-bold text-danger">₱{{ number_format($ref->requested_amount, 2) }}</td>
                                <td><span class="badge bg-info text-dark">{{ $ref->refund_type }}</span></td>
                                <td>
                                    @if($ref->approval_status === 'APPROVED')
                                        <span class="badge bg-success">APPROVED</span>
                                    @elseif($ref->approval_status === 'REQUESTED')
                                        <span class="badge bg-warning text-dark">PENDING REVIEW</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $ref->approval_status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ref->processing_status === 'PROCESSED')
                                        <span class="badge bg-success">PROCESSED</span>
                                    @elseif($ref->processing_status === 'REVERSED')
                                        <span class="badge bg-dark">REVERSED</span>
                                    @else
                                        <span class="badge bg-warning text-dark">PENDING</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $ref->reason }}</small></td>
                                <td class="text-end">
                                    @if($ref->approval_status === 'REQUESTED')
                                        <form method="POST" action="{{ route('admin.finance.refunds.approve', $ref) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                    @elseif($ref->approval_status === 'APPROVED' && $ref->processing_status !== 'PROCESSED')
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#processRefundModal{{ $ref->id }}">Process</button>
                                    @endif
                                </td>
                            </tr>

                            <!-- Process Modal -->
                            @if($ref->approval_status === 'APPROVED' && $ref->processing_status !== 'PROCESSED')
                            <div class="modal fade" id="processRefundModal{{ $ref->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('admin.finance.refunds.process', $ref) }}">
                                        @csrf
                                        <div class="modal-content text-start">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Process Refund {{ $ref->refund_number }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Transaction / Voucher Reference <span class="text-danger">*</span></label>
                                                    <input type="text" name="transaction_reference" class="form-control" required placeholder="e.g. GCash Ref 889977 or Check #1001">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Confirm Payout & Post Ledger</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No refund requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $refunds->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Create Refund Modal -->
<div class="modal fade" id="createRefundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.finance.refunds.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Refund Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Verified Payment <span class="text-danger">*</span></label>
                        <select name="payment_id" class="form-select" required>
                            <option value="">-- Select Payment --</option>
                            @foreach($payments as $p)
                                <option value="{{ $p->id }}">{{ $p->payment_number }} - {{ $p->customer->full_name }} (₱{{ number_format($p->amount, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Refund Amount (PHP) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="requested_amount" class="form-control" required placeholder="0.00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payout Type <span class="text-danger">*</span></label>
                        <select name="refund_type" class="form-select" required>
                            <option value="CASH">Cash</option>
                            <option value="BANK">Bank Deposit</option>
                            <option value="WALLET">E-Wallet (GCash/Maya)</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Reason / Justification <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" required placeholder="Explain why refund is requested..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Refund Request</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
