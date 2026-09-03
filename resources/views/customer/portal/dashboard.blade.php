@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Subscriber Self-Service Portal</h1>
            <p class="text-muted small">Welcome, {{ $customer->full_name }}! Account #: <strong>{{ $customer->customer_number }}</strong></p>
        </div>
        <div>
            @if($service_accounts->count() > 1)
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Switch Service Account
                    </button>
                    <ul class="dropdown-menu">
                        @foreach($service_accounts as $sa)
                            <li><a class="dropdown-menu-item dropdown-item" href="{{ route('portal.dashboard') }}?account_id={{ $sa->id }}">{{ $sa->account_number }} - {{ $sa->service_address }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    @if($delinquency_status === 'SUSPENDED')
        <div class="alert alert-danger shadow-sm mb-4" role="alert">
            <h5 class="alert-heading fw-bold"><i class="bi bi-slash-circle me-2"></i> Account Suspended</h5>
            <p class="mb-1">Your internet service is currently suspended due to an overdue balance of <strong>₱{{ number_format($overdue_balance, 2) }}</strong>.</p>
            <hr>
            <div class="d-flex justify-content-between align-items-center">
                <span>Pay your overdue balance to automatically request immediate service reconnection.</span>
                <a href="{{ route('portal.invoices') }}" class="btn btn-light text-danger fw-bold">Pay Now</a>
            </div>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold small">Current Account Balance</h6>
                    <h3 class="fw-bold mb-0 text-primary">₱{{ number_format($current_balance, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold small">Active Subscription</h6>
                    <h4 class="fw-bold mb-0 text-dark">{{ $active_subscription->package->name ?? 'No Active Plan' }}</h4>
                    <small class="text-muted">{{ $active_subscription ? '₱' . number_format($active_subscription->monthly_rate, 2) . '/mo' : '' }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold small">Service Status</h6>
                    <h4 class="fw-bold mb-0">
                        @if($delinquency_status === 'SUSPENDED')
                            <span class="badge bg-danger">SUSPENDED</span>
                        @elseif($active_subscription && $active_subscription->status === 'ACTIVE')
                            <span class="badge bg-success">ACTIVE</span>
                        @else
                            <span class="badge bg-secondary">{{ $active_subscription->status ?? 'INACTIVE' }}</span>
                        @endif
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold small">Available Credits</h6>
                    <h3 class="fw-bold mb-0 text-success">₱{{ number_format($available_credits, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('portal.invoices') }}" class="btn btn-outline-primary p-3 w-100 text-center shadow-sm">
                <i class="bi bi-receipt fs-3 d-block mb-1"></i>
                <span class="fw-bold">My Invoices</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('portal.payments') }}" class="btn btn-outline-success p-3 w-100 text-center shadow-sm">
                <i class="bi bi-credit-card fs-3 d-block mb-1"></i>
                <span class="fw-bold">Payment History</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('portal.requests') }}" class="btn btn-outline-info p-3 w-100 text-center shadow-sm">
                <i class="bi bi-tools fs-3 d-block mb-1"></i>
                <span class="fw-bold">Service Requests</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('portal.services') }}" class="btn btn-outline-secondary p-3 w-100 text-center shadow-sm">
                <i class="bi bi-wifi fs-3 d-block mb-1"></i>
                <span class="fw-bold">My Broadband Plan</span>
            </a>
        </div>
    </div>
</div>
@endsection
