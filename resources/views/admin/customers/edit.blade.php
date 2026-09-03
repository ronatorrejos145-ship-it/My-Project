@extends('layouts.app')

@section('title', 'Edit Customer - CRM')

@section('content')
<div class="p-6 max-w-4xl">
    <div class="pb-6 border-b border-slate-200 dark:border-slate-800">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Customer #{{ $customer->customer_number }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Update customer master profile details.</p>
    </div>

    <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="font-bold text-slate-800 dark:text-white text-sm">Customer Details</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Customer Type</label>
                    <select name="customer_type" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="RESIDENTIAL" {{ $customer->customer_type === 'RESIDENTIAL' ? 'selected' : '' }}>RESIDENTIAL</option>
                        <option value="INDIVIDUAL" {{ $customer->customer_type === 'INDIVIDUAL' ? 'selected' : '' }}>INDIVIDUAL</option>
                        <option value="BUSINESS" {{ $customer->customer_type === 'BUSINESS' ? 'selected' : '' }}>BUSINESS</option>
                        <option value="CORPORATE" {{ $customer->customer_type === 'CORPORATE' ? 'selected' : '' }}>CORPORATE</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Branch Location</label>
                    <select name="branch_id" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $customer->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $customer->first_name) }}" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $customer->last_name) }}" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Business / Company Name</label>
                    <input type="text" name="business_name" value="{{ old('business_name', $customer->business_name) }}" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Primary Mobile Phone *</label>
                    <input type="text" name="primary_phone" value="{{ old('primary_phone', $customer->primary_phone) }}" required class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Acquisition Source</label>
                    <input type="text" name="acquisition_source" value="{{ old('acquisition_source', $customer->acquisition_source) }}" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <a href="{{ route('admin.customers.show', $customer) }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                Update Profile
            </button>
        </div>
    </form>
</div>
@endsection
