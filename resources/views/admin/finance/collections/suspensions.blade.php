@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Suspension Queue & Service Disconnections</h1>
            <p class="text-muted small">Review suspension-eligible delinquent accounts, execute commercial disconnections, and track provider actions</p>
        </div>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#requestSuspensionModal">
            <i class="bi bi-slash-circle me-1"></i> Request Account Suspension
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
                            <th>Request #</th>
                            <th>Subscriber</th>
                            <th>Delinquency Amount</th>
                            <th>Days Overdue</th>
                            <th>Approval Status</th>
                            <th>Network Action</th>
                            <th>Reason</th>
                            <th>Requested At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td><code>{{ $req->request_number }}</code></td>
                                <td class="fw-bold">{{ $req->customer->full_name }}</td>
                                <td class="fw-bold text-danger">₱{{ number_format($req->delinquency_amount, 2) }}</td>
                                <td>{{ $req->days_overdue }} days</td>
                                <td><span class="badge bg-success">{{ $req->approval_status }}</span></td>
                                <td><span class="badge bg-info text-dark">{{ $req->network_action_status }}</span></td>
                                <td><small class="text-muted">{{ $req->reason }}</small></td>
                                <td>{{ $req->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No suspension requests found.</td>
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
<div class="modal fade" id="requestSuspensionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.finance.collections.suspensions.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Service Suspension</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Eligible Subscriber <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Select Subscriber --</option>
                            @foreach($eligibleCustomers as $c)
                                <option value="{{ $c->id }}">{{ $c->customer_number }} - {{ $c->full_name }} (Overdue: ₱{{ number_format($c->collectionAccount->overdue_amount ?? 0, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Suspension Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" required placeholder="Reason for disconnect request..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Execute Suspension</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
