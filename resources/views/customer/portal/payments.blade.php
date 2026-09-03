@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <h1 class="h3 mb-1 text-gray-800">My Payment Receipts & History</h1>
    <p class="text-muted small mb-4">View verified payment submissions and access official receipts</p>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Payment #</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Reference #</th>
                            <th>Official Receipt</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                            <tr>
                                <td><code>{{ $p->payment_number }}</code></td>
                                <td><span class="badge bg-info text-dark">{{ $p->payment_method_code }}</span></td>
                                <td class="fw-bold text-success">₱{{ number_format($p->amount, 2) }}</td>
                                <td><code>{{ $p->reference_number ?? 'N/A' }}</code></td>
                                <td>
                                    @if($p->receipt)
                                        <span class="badge bg-primary">{{ $p->receipt->receipt_number }}</span>
                                    @else
                                        <span class="text-muted">Pending</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-success">{{ $p->status }}</span></td>
                                <td>{{ $p->payment_date->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No payment records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
