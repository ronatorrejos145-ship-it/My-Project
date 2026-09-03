@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('portal.invoices') }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Invoices</a>
            <h1 class="h3 mb-0 text-gray-800 mt-1">Invoice {{ $invoice->invoice_number }}</h1>
        </div>
        <a href="{{ route('portal.invoices.pdf', $invoice) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Download PDF
        </a>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-muted text-uppercase fw-semibold small">Invoice Overview</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Status:</span>
                        <span class="badge bg-secondary fs-6">{{ $invoice->status }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Due Date:</span>
                        <strong>{{ $invoice->due_date->format('M d, Y') }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Amount:</span>
                        <strong class="fs-5">₱{{ number_format($invoice->total_amount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Paid Amount:</span>
                        <strong class="text-success">₱{{ number_format($invoice->paid_amount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Balance Due:</span>
                        <strong class="text-danger fs-5">₱{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-gray-800">Line Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end fw-bold">₱{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
