@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Payment Arrangements & Installment Plans</h1>
            <p class="text-muted small">Manage structured debt payment plans, installment schedules, and approvals</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createArrangementModal">
            <i class="bi bi-calendar-check me-1"></i> New Payment Arrangement
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
                            <th>Arrangement #</th>
                            <th>Subscriber</th>
                            <th>Total Debt</th>
                            <th>Down Payment</th>
                            <th>Installments</th>
                            <th>Remaining</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arrangements as $arr)
                            <tr>
                                <td><code>{{ $arr->arrangement_number }}</code></td>
                                <td class="fw-bold">{{ $arr->customer->full_name }}</td>
                                <td class="fw-bold">₱{{ number_format($arr->total_amount, 2) }}</td>
                                <td>₱{{ number_format($arr->down_payment_amount, 2) }}</td>
                                <td>{{ $arr->paid_installments }} / {{ $arr->total_installments }} (₱{{ number_format($arr->installment_amount, 2) }}/mo)</td>
                                <td class="fw-bold text-danger">₱{{ number_format($arr->remaining_balance, 2) }}</td>
                                <td>
                                    @if($arr->status === 'ACTIVE')
                                        <span class="badge bg-success">ACTIVE</span>
                                    @elseif($arr->status === 'PENDING_APPROVAL')
                                        <span class="badge bg-warning text-dark">PENDING APPROVAL</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $arr->status }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($arr->status === 'PENDING_APPROVAL')
                                        <form method="POST" action="{{ route('admin.finance.collections.arrangements.approve', $arr) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No payment arrangements found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $arrangements->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="createArrangementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.finance.collections.arrangements.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Payment Arrangement</h5>
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
                        <label class="form-label">Total Debt Amount (PHP) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="total_amount" class="form-control" required placeholder="0.00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Down Payment (PHP)</label>
                        <input type="number" step="0.01" name="down_payment_amount" class="form-control" value="0.00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Installments Count (Months) <span class="text-danger">*</span></label>
                        <input type="number" name="installments_count" class="form-control" required value="3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Arrangement notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Arrangement</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
