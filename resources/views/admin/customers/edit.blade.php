@extends('layouts.app')

@section('title', 'Edit Customer')
@section('header', 'Edit Customer: ' . $customer->contact_person)

@section('content')
<div class="max-w-3xl bg-white p-6 rounded shadow-sm border border-gray-200">
    <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Customer Number</label>
                <input type="text" value="{{ $customer->customer_number }}" disabled class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded font-mono text-gray-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Account Number</label>
                <input type="text" value="{{ $customer->account_number }}" disabled class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded font-mono text-gray-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Customer Type</label>
                <select name="customer_type" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
                    @foreach(['RESIDENTIAL', 'BUSINESS', 'CORPORATE', 'GOVERNMENT'] as $type)
                    <option value="{{ $type }}" {{ $customer->customer_type === $type ? 'selected' : '' }}>{{ ucfirst(strtolower($type)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
                    @foreach($statuses as $st)
                    <option value="{{ $st->value }}" {{ ($customer->status instanceof App\Enums\CustomerStatus ? $customer->status->value : $customer->status) === $st->value ? 'selected' : '' }}>{{ $st->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Contact Person Name</label>
            <input type="text" name="contact_person" value="{{ old('contact_person', $customer->contact_person) }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Primary Phone</label>
                <input type="text" name="primary_phone" value="{{ old('primary_phone', $customer->primary_phone) }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Installation Address</label>
            <textarea name="installation_address" rows="2" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">{{ old('installation_address', $customer->installation_address) }}</textarea>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 border border-gray-300 rounded text-sm text-gray-700">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-sky-600 text-white rounded text-sm font-medium">Update Customer</button>
        </div>
    </form>
</div>
@endsection
