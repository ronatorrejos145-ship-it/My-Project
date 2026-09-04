@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Discounts & Promotional Campaigns</h1>
            <p class="text-muted small">Configure discount rules, promotional percentage/fixed offers, and stacking rules</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDiscountModal">
            <i class="bi bi-plus-circle me-1"></i> New Discount Rule
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
                            <th>Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Min Invoice</th>
                            <th>Max Cap</th>
                            <th>Stackable</th>
                            <th>Priority</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($discounts as $d)
                            <tr>
                                <td><code>{{ $d->code }}</code></td>
                                <td class="fw-bold">{{ $d->name }}</td>
                                <td><span class="badge bg-info text-dark">{{ $d->discount_type }}</span></td>
                                <td class="fw-bold">{{ $d->discount_type === 'PERCENTAGE' ? $d->value . '%' : '₱' . number_format($d->value, 2) }}</td>
                                <td>{{ $d->min_invoice_amount ? '₱' . number_format($d->min_invoice_amount, 2) : 'None' }}</td>
                                <td>{{ $d->max_discount_amount ? '₱' . number_format($d->max_discount_amount, 2) : 'No Cap' }}</td>
                                <td>
                                    @if($d->stacking_allowed)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $d->priority }}</td>
                                <td><span class="badge bg-success">{{ $d->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No discount rules configured.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $discounts->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="createDiscountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('admin.finance.discounts.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Discount Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Discount Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Summer Promo 10%">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                        <select name="discount_type" class="form-select" required>
                            <option value="PERCENTAGE">Percentage (%)</option>
                            <option value="FIXED_AMOUNT">Fixed Amount (PHP)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="value" class="form-control" required placeholder="10 or 100.00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Priority (1 = Lowest)</label>
                        <input type="number" name="priority" class="form-control" value="1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Max Cap Amount (Optional PHP)</label>
                        <input type="number" step="0.01" name="max_discount_amount" class="form-control" placeholder="0.00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Min Invoice Subtotal (Optional PHP)</label>
                        <input type="number" step="0.01" name="min_invoice_amount" class="form-control" placeholder="0.00">
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="stacking_allowed" id="stackingAllowed" value="1">
                            <label class="form-check-label" for="stackingAllowed">
                                Stacking Allowed (Can combine with other discounts)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Rule</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
