@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Promises to Pay Tracker</h1>
            <p class="text-muted small">Track subscriber payment commitments, fulfillment status, and broken promises</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPromiseModal">
            <i class="bi bi-clock-history me-1"></i> New Promise to Pay
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
                            <th>Promise #</th>
                            <th>Subscriber</th>
                            <th>Promised Amount</th>
                            <th>Promised Date</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promises as $p)
                            <tr>
                                <td><code>{{ $p->promise_number }}</code></td>
                                <td class="fw-bold">{{ $p->customer->full_name }} ({{ $p->customer->customer_number }})</td>
                                <td class="fw-bold text-success">₱{{ number_format($p->promised_amount, 2) }}</td>
                                <td>{{ $p->promised_date->format('M d, Y') }}</td>
                                <td>
                                    @if($p->status === 'ACTIVE')
                                        <span class="badge bg-info text-dark">ACTIVE</span>
                                    @elseif($p->status === 'FULFILLED')
                                        <span class="badge bg-success">FULFILLED</span>
                                    @elseif($p->status === 'BROKEN')
                                        <span class="badge bg-danger">BROKEN</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $p->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $p->createdBy->name ?? 'System' }}</td>
                                <td><small class="text-muted">{{ $p->notes }}</small></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No promises to pay found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $promises->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="createPromiseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.finance.collections.promises.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Promise to Pay</h5>
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
                    <div class="col-md-6">
                        <label class="form-label">Promised Amount (PHP) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="promised_amount" class="form-control" required placeholder="0.00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Promised Date <span class="text-danger">*</span></label>
                        <input type="date" name="promised_date" class="form-control" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Collector Remarks</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Record terms of payment agreement..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Promise</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
