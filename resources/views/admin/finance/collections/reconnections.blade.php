@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Reconnection Requests & Service Restoration</h1>
            <p class="text-muted small">Manage service restorations for paid suspended accounts, fee waivers, and network reconnection execution</p>
        </div>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#requestReconnectionModal">
            <i class="bi bi-arrow-repeat me-1"></i> Request Account Reconnection
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
                            <th>Amount Paid</th>
                            <th>Remaining Debt</th>
                            <th>Reconnection Fee</th>
                            <th>Approval Status</th>
                            <th>Network Action</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td><code>{{ $req->request_number }}</code></td>
                                <td class="fw-bold">{{ $req->customer->full_name }}</td>
                                <td class="fw-bold text-success">₱{{ number_format($req->amount_paid, 2) }}</td>
                                <td>₱{{ number_format($req->amount_remaining, 2) }}</td>
                                <td>
                                    ₱{{ number_format($req->reconnection_fee, 2) }}
                                    @if($req->reconnection_fee_waived)
                                        <span class="badge bg-warning text-dark ms-1">WAIVED</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-success">{{ $req->approval_status }}</span></td>
                                <td><span class="badge bg-info text-dark">{{ $req->network_action_status }}</span></td>
                                <td>{{ $req->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No reconnection requests found.</td>
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
<div class="modal fade" id="requestReconnectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.finance.collections.reconnections.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Service Reconnection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Suspended Subscriber <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Select Suspended Subscriber --</option>
                            @foreach($suspendedCustomers as $c)
                                <option value="{{ $c->id }}">{{ $c->customer_number }} - {{ $c->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reconnection Fee (PHP)</label>
                        <input type="number" step="0.01" name="reconnection_fee" class="form-control" value="0.00">
                    </div>
                    <div class="col-md-6 d-flex align-items-end mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="waive_fee" id="waiveFee" value="1">
                            <label class="form-check-label" for="waiveFee">
                                Waive Reconnection Fee
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Restore Service</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
