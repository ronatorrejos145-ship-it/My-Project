@extends('layouts.app')

@section('title', 'Add Service Package - Catalog')

@section('content')
<div class="p-6 max-w-4xl">
    <div class="pb-6 border-b border-slate-200 dark:border-slate-800">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Create New Service Package</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Configure broadband plan parameters, pricing, technology, and equipment rules.</p>
    </div>

    <form method="POST" action="{{ route('admin.packages.store') }}" class="mt-6 space-y-6">
        @csrf

        <!-- 1. Basic Information -->
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="font-bold text-slate-800 dark:text-white text-sm">1. Basic Plan Identification</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Package Code *</label>
                    <input type="text" name="package_code" value="{{ old('package_code') }}" required placeholder="e.g. HOME-100" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Package Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Fiber Plan 100 Mbps" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Category</label>
                    <select name="service_category_id" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="">No Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Package Type *</label>
                    <select name="package_type" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="RESIDENTIAL">RESIDENTIAL</option>
                        <option value="BUSINESS">BUSINESS</option>
                        <option value="CORPORATE">CORPORATE</option>
                        <option value="PREPAID">PREPAID</option>
                        <option value="POSTPAID">POSTPAID</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Technology *</label>
                    <select name="technology" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="FIBER">FIBER</option>
                        <option value="FTTH">FTTH</option>
                        <option value="WIRELESS">WIRELESS</option>
                        <option value="RADIO">RADIO</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Status *</label>
                    <select name="status" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="DRAFT">DRAFT</option>
                        <option value="INACTIVE">INACTIVE</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Speeds & Pricing -->
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="font-bold text-slate-800 dark:text-white text-sm">2. Speeds & Commercial Pricing</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Download Speed *</label>
                    <input type="number" name="download_speed" value="{{ old('download_speed', 100) }}" required class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Upload Speed *</label>
                    <input type="number" name="upload_speed" value="{{ old('upload_speed', 100) }}" required class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Speed Unit *</label>
                    <select name="speed_unit" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="Mbps">Mbps</option>
                        <option value="Gbps">Gbps</option>
                        <option value="Kbps">Kbps</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Monthly Recurring Price (₱) *</label>
                    <input type="number" step="0.01" name="base_price" value="{{ old('base_price', 1499.00) }}" required class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Installation Fee (₱)</label>
                    <input type="number" step="0.01" name="installation_fee" value="{{ old('installation_fee', 1500.00) }}" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Contract Lock-in Period (Months)</label>
                    <input type="number" name="contract_period_months" value="{{ old('contract_period_months', 24) }}" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <a href="{{ route('admin.packages.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Save Package</button>
        </div>
    </form>
</div>
@endsection
