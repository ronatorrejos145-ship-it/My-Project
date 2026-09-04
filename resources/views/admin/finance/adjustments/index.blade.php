@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Financial Adjustments & Manual Approvals</h1>
            <p class="text-muted small">Manage manual debit/credit adjustments and supervisor approval queue</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAdjustmentModal">
            <i class="bi bi-plus-circle me-1"></i> New Financial Adjustment
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($pendingApprovals->count() > 0)
        <div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-gray-800"><i class="bi bi-exclamation-triangle text-warning me-2"></i> Pending Approval Queue</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Entity / Type</th>
                                <th>Amount</th>
                                <th>Required Authority</th>
                                <th>Requested By</th>
                                <th>Comments</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingApprovals as $req)
                                <tr>
                                    <td><span class="fw-bold">{{ $req->request_type }}</span></td>
                                    <td class="fw-bold">₱{{ number_format($req->requested_amount, 2) }}</td>
                                    <td><span class="badge bg-warning text-dark">{{ $req->required_role }}</span></td>
                                    <td>{{ $req->requestedBy->name ?? 'System' }}</td>
                                    <td><small class="text-muted">{{ $req->comments }}</small></td>
                                    <td class="text-end">
                                        @if($req->entity_type === \App\Models\FinancialAdjustment::class)
                                            <form method="POST" action="{{ route('admin.finance.adjustments.approve', $req->entity_id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-gray-800">Financial Adjustments History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Adjustment #</th>
                            <th>Subscriber</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adj)
                            <tr>
                                <td class="fw-bold">{{ $adj->adjustment_number }}</td>
                                <td>{{ $adj->customer->full_name }} ({{ $adj->customer->customer_number }})</td>
                                <td><span class="badge bg-info text-dark">{{ $adj->adjustment_type }}</span></td>
                                <td class="fw-bold">₱{{ number_format($adj->amount, 2) }}</td>
                                <td>
                                    @if($adj->status === 'POSTED')
                                        <span class="badge bg-success">POSTED</span>
                                    @elseif($adj->status === 'PENDING_APPROVAL')
                                        <span class="badge bg-warning text-dark">PENDING APPROVAL</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $adj->status }}</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $adj->reason }}</small></td>
                                <td>{{ $adj->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No financial adjustments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $adjustments->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="createAdjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.finance.adjustments.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Financial Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Select Subscriber --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->customer_number }} - {{ $c->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                        <select name="adjustment_type" class="form-select" required>
                            <option value="CREDIT_ADJUSTMENT">Credit Adjustment (Reduce Balance)</option>
                            <option value="DEBIT_ADJUSTMENT">Debit Adjustment (Increase Balance)</option>
                            <option value="WRITE_OFF">Bad Debt Write-Off</option>
                            <option value="CORRECTION">Billing Correction</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount (PHP) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Reason / Justification <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" required placeholder="Explain financial justification..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Adjustment</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
