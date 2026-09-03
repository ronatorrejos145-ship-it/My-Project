@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Subscriber Account Credits</h1>
            <p class="text-muted small">Issue account credits, review balances, and apply credits against unpaid invoices</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueCreditModal">
            <i class="bi bi-plus-circle me-1"></i> Issue Account Credit
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
                            <th>Credit #</th>
                            <th>Subscriber</th>
                            <th>Type</th>
                            <th>Total Credit</th>
                            <th>Remaining</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Issue Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($credits as $c)
                            <tr>
                                <td><code>{{ $c->credit_number }}</code></td>
                                <td>{{ $c->customer->full_name }} ({{ $c->customer->customer_number }})</td>
                                <td><span class="badge bg-info text-dark">{{ $c->credit_type }}</span></td>
                                <td class="fw-bold">₱{{ number_format($c->total_amount, 2) }}</td>
                                <td class="fw-bold text-success">₱{{ number_format($c->remaining_amount, 2) }}</td>
                                <td><span class="badge bg-success">{{ $c->status }}</span></td>
                                <td><small class="text-muted">{{ $c->reason }}</small></td>
                                <td>{{ $c->issue_date->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No account credits found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $credits->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="issueCreditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.finance.credits.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Issue Subscriber Credit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Select Subscriber --</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}">{{ $cust->customer_number }} - {{ $cust->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Credit Type <span class="text-danger">*</span></label>
                        <select name="credit_type" class="form-select" required>
                            <option value="OUTAGE">Service Outage Compensation</option>
                            <option value="GOODWILL">Goodwill Gesture</option>
                            <option value="REFERRAL">Referral Bonus</option>
                            <option value="BILLING_CORRECTION">Billing Correction</option>
                            <option value="MANUAL">Manual Adjustment</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount (PHP) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Reason / Justification <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" required placeholder="Reason for issuing credit..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Issue Credit</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
