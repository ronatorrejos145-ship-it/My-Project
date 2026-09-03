@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <h1 class="h3 mb-1 text-gray-800">My Invoices</h1>
    <p class="text-muted small mb-4">View issued billing statements, download official PDFs, and pay outstanding balances</p>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Billing Period</th>
                            <th>Total Amount</th>
                            <th>Paid</th>
                            <th>Balance Due</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td>
                                    <a href="{{ route('portal.invoices.show', $inv) }}" class="fw-bold text-decoration-none">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td><small class="text-muted">{{ $inv->billing_period_start->format('M d') }} – {{ $inv->billing_period_end->format('M d, Y') }}</small></td>
                                <td class="fw-bold">₱{{ number_format($inv->total_amount, 2) }}</td>
                                <td>₱{{ number_format($inv->paid_amount, 2) }}</td>
                                <td class="fw-bold text-danger">₱{{ number_format($inv->total_amount - $inv->paid_amount, 2) }}</td>
                                <td>{{ $inv->due_date->format('M d, Y') }}</td>
                                <td>
                                    @if($inv->status === 'PAID')
                                        <span class="badge bg-success">PAID</span>
                                    @elseif($inv->status === 'OVERDUE')
                                        <span class="badge bg-danger">OVERDUE</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ $inv->status }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('portal.invoices.pdf', $inv) }}" class="btn btn-sm btn-outline-secondary me-1">
                                        <i class="bi bi-download me-1"></i> PDF
                                    </a>
                                    <a href="{{ route('portal.invoices.show', $inv) }}" class="btn btn-sm btn-primary">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No invoices found for your account.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
