@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">My Service Requests</h1>
            <p class="text-muted small">Submit upgrade, downgrade, relocation, and equipment replacement requests</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRequestModal">
            <i class="bi bi-plus-circle me-1"></i> New Service Request
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
                            <th>Request Type</th>
                            <th>Target Plan</th>
                            <th>Status</th>
                            <th>Submitted Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td><code>{{ $req->request_number }}</code></td>
                                <td><span class="badge bg-info text-dark">{{ $req->request_type }}</span></td>
                                <td>{{ $req->targetPackage->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $req->status }}</span></td>
                                <td>{{ $req->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No service requests submitted yet.</td>
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
<div class="modal fade" id="createRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('portal.requests.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit Self-Service Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Request Type <span class="text-danger">*</span></label>
                        <select name="request_type" class="form-select" required>
                            <option value="UPGRADE">Package Upgrade</option>
                            <option value="DOWNGRADE">Package Downgrade</option>
                            <option value="RELOCATION">Service Relocation</option>
                            <option value="EQUIPMENT_REPLACEMENT">Equipment Replacement</option>
                            <option value="TERMINATION">Service Termination</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Desired Plan (If Upgrade/Downgrade)</label>
                        <select name="target_package_id" class="form-select">
                            <option value="">-- Select Target Plan --</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}">{{ $pkg->name }} - ₱{{ number_format($pkg->monthly_rate, 2) }}/mo</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Notes / Details</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Provide additional details or relocation address..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
