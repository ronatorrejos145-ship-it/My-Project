@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Collections Terminal & Accounts Receivable Aging</h1>
            <p class="text-muted small">Monitor delinquent subscribers, AR aging buckets, and collection work queues</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.collections.promises') }}" class="btn btn-outline-primary"><i class="bi bi-clock-history me-1"></i> Promises to Pay</a>
            <a href="{{ route('admin.finance.collections.arrangements') }}" class="btn btn-outline-success"><i class="bi bi-calendar-check me-1"></i> Payment Arrangements</a>
            <a href="{{ route('admin.finance.collections.suspensions') }}" class="btn btn-outline-danger"><i class="bi bi-slash-circle me-1"></i> Suspension Queue</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold small">Total Overdue AR</h6>
                    <h3 class="fw-bold mb-0 text-danger">₱{{ number_format($stats['total_overdue'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold small">Suspension Eligible Accounts</h6>
                    <h3 class="fw-bold mb-0 text-warning">
                        {{ $stats['eligible_suspension'] ?? 0 }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold small">Suspended Subscribers</h6>
                    <h3 class="fw-bold mb-0 text-dark">
                        {{ $stats['suspended_count'] ?? 0 }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold small">Active Promises to Pay</h6>
                    <h3 class="fw-bold mb-0 text-info">
                        {{ $stats['promises_active'] ?? 0 }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Aging Buckets Row -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-gray-800">Accounts Receivable Aging Breakdown</h5>
        </div>
        <div class="card-body">
            <div class="row text-center g-2">
                <div class="col">
                    <div class="p-2 border rounded bg-light">
                        <small class="text-muted d-block">Current</small>
                        <strong class="fs-6">₱{{ number_format($agingBuckets['CURRENT'] ?? 0, 2) }}</strong>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 border rounded bg-light">
                        <small class="text-muted d-block">1–7 Days</small>
                        <strong class="fs-6 text-primary">₱{{ number_format($agingBuckets['1_7_DAYS'] ?? 0, 2) }}</strong>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 border rounded bg-light">
                        <small class="text-muted d-block">8–15 Days</small>
                        <strong class="fs-6 text-warning">₱{{ number_format($agingBuckets['8_15_DAYS'] ?? 0, 2) }}</strong>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 border rounded bg-light">
                        <small class="text-muted d-block">16–30 Days</small>
                        <strong class="fs-6 text-danger">₱{{ number_format($agingBuckets['16_30_DAYS'] ?? 0, 2) }}</strong>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 border rounded bg-light">
                        <small class="text-muted d-block">31–60 Days</small>
                        <strong class="fs-6 text-danger">₱{{ number_format($agingBuckets['31_60_DAYS'] ?? 0, 2) }}</strong>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 border rounded bg-light">
                        <small class="text-muted d-block">61–90 Days</small>
                        <strong class="fs-6 text-dark">₱{{ number_format($agingBuckets['61_90_DAYS'] ?? 0, 2) }}</strong>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 border rounded bg-light">
                        <small class="text-muted d-block">90+ Days</small>
                        <strong class="fs-6 text-dark">₱{{ number_format($agingBuckets['90_PLUS_DAYS'] ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-gray-800">Delinquent Subscribers Work Queue</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Subscriber</th>
                            <th>Status</th>
                            <th>Days Overdue</th>
                            <th>Overdue Amount</th>
                            <th>Total Outstanding</th>
                            <th>Risk Level</th>
                            <th>Last Action</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $acc)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $acc->customer->full_name }}</div>
                                    <small class="text-muted">{{ $acc->customer->customer_number }}</small>
                                </td>
                                <td>
                                    @if($acc->delinquency_status === 'SUSPENDED')
                                        <span class="badge bg-danger">SUSPENDED</span>
                                    @elseif($acc->delinquency_status === 'SUSPENSION_ELIGIBLE')
                                        <span class="badge bg-warning text-dark">SUSPENSION ELIGIBLE</span>
                                    @elseif($acc->delinquency_status === 'COLLECTION_WARNING')
                                        <span class="badge bg-info text-dark">WARNING</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $acc->delinquency_status }}</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-danger">{{ $acc->days_overdue }} days</td>
                                <td class="fw-bold text-danger">₱{{ number_format($acc->overdue_amount, 2) }}</td>
                                <td>₱{{ number_format($acc->total_outstanding_amount, 2) }}</td>
                                <td><span class="badge bg-secondary">{{ $acc->risk_level }}</span></td>
                                <td><small class="text-muted">{{ $acc->last_collection_action ?? 'None' }}</small></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#actionModal{{ $acc->id }}">Record Action</button>
                                </td>
                            </tr>

                            <!-- Modal -->
                            <div class="modal fade" id="actionModal{{ $acc->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('admin.finance.collections.action', $acc) }}">
                                        @csrf
                                        <div class="modal-content text-start">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Record Collection Action for {{ $acc->customer->full_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label">Action Type <span class="text-danger">*</span></label>
                                                    <select name="action_type" class="form-select" required>
                                                        <option value="PHONE_CALL">Phone Call Follow-Up</option>
                                                        <option value="SMS_REMINDER">SMS Reminder Sent</option>
                                                        <option value="EMAIL_REMINDER">Email Notice Sent</option>
                                                        <option value="SUSPENSION_WARNING">Formal Suspension Warning</option>
                                                        <option value="FIELD_VISIT">Field Collector Visit</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Next Action Date</label>
                                                    <input type="date" name="next_action_date" class="form-control">
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Collector Remarks</label>
                                                    <textarea name="notes" class="form-control" rows="2" placeholder="Record subscriber conversation or outcome..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Action</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No delinquent accounts in queue.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $accounts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
