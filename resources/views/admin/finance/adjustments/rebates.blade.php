@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Rebate Programs & Customer Rewards</h1>
            <p class="text-muted small">Manage subscriber referral rebates and loyalty reward programs</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#referralRebateModal">
            <i class="bi bi-gift me-1"></i> Award Referral Rebate
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
                            <th>Rebate #</th>
                            <th>Referring Subscriber</th>
                            <th>Referred Subscriber</th>
                            <th>Reward Amount</th>
                            <th>Status</th>
                            <th>Awarded Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rebates as $r)
                            <tr>
                                <td><code>{{ $r->rebate_number }}</code></td>
                                <td class="fw-bold">{{ $r->customer->full_name }} ({{ $r->customer->customer_number }})</td>
                                <td>{{ $r->referredCustomer->full_name ?? 'N/A' }}</td>
                                <td class="fw-bold text-success">₱{{ number_format($r->amount, 2) }}</td>
                                <td><span class="badge bg-success">{{ $r->status }}</span></td>
                                <td>{{ $r->awarded_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No rebates awarded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $rebates->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="referralRebateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.finance.rebates.referral') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Award Referral Rebate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Referring Subscriber (Recipient) <span class="text-danger">*</span></label>
                        <select name="referring_customer_id" class="form-select" required>
                            <option value="">-- Select Recipient --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->customer_number }} - {{ $c->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">New Referred Subscriber <span class="text-danger">*</span></label>
                        <select name="referred_customer_id" class="form-select" required>
                            <option value="">-- Select Referred Subscriber --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->customer_number }} - {{ $c->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Reward Amount (PHP) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" required value="500.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Process Rebate</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
