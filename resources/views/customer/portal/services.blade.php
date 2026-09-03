@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <h1 class="h3 mb-1 text-gray-800">My Broadband Subscriptions</h1>
    <p class="text-muted small mb-4">View active service accounts, current speed package snapshots, and request plan changes</p>

    @foreach($serviceAccounts as $sa)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-gray-800">Service Account: {{ $sa->account_number }}</h5>
                <span class="badge bg-success">{{ $sa->status }}</span>
            </div>
            <div class="card-body">
                <p><strong>Installation Address:</strong> {{ $sa->service_address }}</p>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Subscription #</th>
                                <th>Package Name</th>
                                <th>Download / Upload</th>
                                <th>Monthly Rate</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sa->subscriptions as $sub)
                                <tr>
                                    <td><code>{{ $sub->subscription_number }}</code></td>
                                    <td class="fw-bold">{{ $sub->package->name ?? 'Custom Plan' }}</td>
                                    <td>{{ $sub->package->download_speed_mbps ?? 'N/A' }} Mbps / {{ $sub->package->upload_speed_mbps ?? 'N/A' }} Mbps</td>
                                    <td class="fw-bold text-success">₱{{ number_format($sub->monthly_rate, 2) }}/mo</td>
                                    <td><span class="badge bg-success">{{ $sub->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
