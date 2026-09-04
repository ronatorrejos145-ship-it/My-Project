@extends('layouts.app')

@section('title', 'Service Package - ' . $package->name)

@section('content')
<div class="p-6">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $package->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                        {{ $package->status }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                        {{ $package->technology }}
                    </span>
                </div>
                <div class="mt-2 flex flex-wrap gap-4 text-xs font-mono text-slate-500">
                    <span>Code: <strong class="text-indigo-600 dark:text-indigo-400">{{ $package->package_code }}</strong></span>
                    <span>Category: <strong>{{ $package->category->name ?? 'General' }}</strong></span>
                    <span>Type: <strong>{{ $package->package_type }}</strong></span>
                    <span>Billing Cycle: <strong>{{ $package->billingCycle->name ?? 'Monthly' }}</strong></span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.packages.edit', $package) }}" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-medium hover:bg-slate-100">
                    Edit Metadata
                </a>
                <a href="{{ route('admin.packages.versions.create', $package) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold">
                    + Publish New Price Version
                </a>
            </div>
        </div>

        <!-- Metric Highlights -->
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-xs text-slate-400">Download Speed</span>
                <p class="font-bold font-mono text-indigo-600 dark:text-indigo-400 text-lg">⚡ {{ $package->download_speed_formatted }}</p>
            </div>
            <div>
                <span class="text-xs text-slate-400">Upload Speed</span>
                <p class="font-bold font-mono text-indigo-600 dark:text-indigo-400 text-lg">⚡ {{ $package->upload_speed_formatted }}</p>
            </div>
            <div>
                <span class="text-xs text-slate-400">Monthly Base Price</span>
                <p class="font-bold font-mono text-slate-800 dark:text-white text-lg">₱{{ number_format($package->base_price, 2) }}</p>
            </div>
            <div>
                <span class="text-xs text-slate-400">Installation Fee</span>
                <p class="font-bold font-mono text-slate-800 dark:text-white text-lg">₱{{ number_format($package->installation_fee, 2) }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Column: Version History -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Package Version History Timeline -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5">
                <div class="flex justify-between items-center pb-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-slate-800 dark:text-white text-base">📜 Version History & Price Auditing</h3>
                    <a href="{{ route('admin.packages.versions.create', $package) }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                        + New Price Version
                    </a>
                </div>

                <div class="mt-4 space-y-4">
                    @forelse($package->versions as $v)
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-800">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                                        Version {{ $v->version_number }}
                                    </span>
                                    <span class="font-bold text-slate-800 dark:text-white text-sm">{{ $v->version_name }}</span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $v->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $v->status }}
                                </span>
                            </div>

                            <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2 font-mono text-xs">
                                <div>Price: <strong>₱{{ number_format($v->price, 2) }}</strong></div>
                                <div>Installation: <strong>₱{{ number_format($v->installation_fee, 2) }}</strong></div>
                                <div>Speeds: <strong>{{ $v->download_speed }} / {{ $v->upload_speed }} Mbps</strong></div>
                                <div>Contract: <strong>{{ $v->contract_period_months }} Mos</strong></div>
                            </div>

                            <div class="mt-2 text-xs text-slate-400">
                                Effective: {{ $v->effective_from?->format('M d, Y') }} → {{ $v->effective_until ? $v->effective_until->format('M d, Y') : 'Present' }}
                                • Reason: <em>"{{ $v->change_reason }}"</em>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4">No version history records found.</p>
                    @endforelse
                </div>
            </div>

            <!-- Equipment Requirements -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-800 dark:text-white text-base pb-3 border-b border-slate-200 dark:border-slate-800">🔌 Hardware & Equipment Requirements</h3>
                <div class="mt-3 divide-y divide-slate-200 dark:divide-slate-800 text-xs">
                    @forelse($package->equipmentRequirements as $eq)
                        <div class="py-2.5 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-slate-800 dark:text-white">{{ $eq->assetModel->model_name ?? 'CPE Unit' }}</span>
                                <div class="text-slate-400">Qty: {{ $eq->quantity }} • {{ $eq->is_included ? 'Included in Plan' : 'Additional Charge' }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $eq->is_required ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $eq->is_required ? 'Mandatory' : 'Optional' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-3">No specific equipment requirements attached.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Sidebar Column -->
        <div class="space-y-6">
            <!-- Features -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-800 dark:text-white text-base pb-3 border-b border-slate-200 dark:border-slate-800">⭐ Package Features</h3>
                <div class="mt-3 space-y-2 text-xs">
                    @forelse($package->features as $feat)
                        <div class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span>{{ $feat->name }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-2">No special features configured.</p>
                    @endforelse
                </div>
            </div>

            <!-- Service Area Availability -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-800 dark:text-white text-base pb-3 border-b border-slate-200 dark:border-slate-800">🗺️ Coverage Availability</h3>
                <div class="mt-3 space-y-2 text-xs">
                    @forelse($package->serviceAreas as $sa)
                        <div class="p-2 bg-slate-50 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-800 font-medium text-slate-700 dark:text-slate-300">
                            {{ $sa->name }}
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-2">Available across all active service areas.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
