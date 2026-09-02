@extends('layouts.app')

@section('title', 'Create Customer')
@section('header', 'Create Customer Account')

@section('content')
<div class="max-w-3xl bg-white p-6 rounded shadow-sm border border-gray-200">
    <form method="POST" action="{{ route('admin.customers.store') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Customer Number</label>
                <input type="text" name="customer_number" value="{{ old('customer_number', 'CUST-' . rand(10000, 99999)) }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Account Number</label>
                <input type="text" name="account_number" value="{{ old('account_number', 'ACC-' . rand(10000, 99999)) }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Customer Type</label>
                <select name="customer_type" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
                    <option value="RESIDENTIAL">Residential</option>
                    <option value="BUSINESS">Business</option>
                    <option value="CORPORATE">Corporate</option>
                    <option value="GOVERNMENT">Government</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Initial Status</label>
                <select name="status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
                    @foreach($statuses as $st)
                    <option value="{{ $st->value }}">{{ $st->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Contact Person Name</label>
            <input type="text" name="contact_person" value="{{ old('contact_person') }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Primary Phone</label>
                <input type="text" name="primary_phone" value="{{ old('primary_phone') }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Installation Address</label>
            <textarea name="installation_address" rows="2" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">{{ old('installation_address') }}</textarea>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 border border-gray-300 rounded text-sm text-gray-700">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-sky-600 text-white rounded text-sm font-medium">Save Customer</button>
        </div>
    </form>
</div>
@endsection
