@extends('layouts.app')

@section('title', 'Edit Company - Master Data')

@section('content')
<div class="p-6 max-w-3xl">
    <div class="pb-6 border-b border-slate-200 dark:border-slate-800">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Company: {{ $company->legal_name }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.companies.update', $company) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Company Code *</label>
                <input type="text" name="code" value="{{ old('code', $company->code) }}" required class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Legal Name *</label>
                <input type="text" name="legal_name" value="{{ old('legal_name', $company->legal_name) }}" required class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Trade Name</label>
                <input type="text" name="trade_name" value="{{ old('trade_name', $company->trade_name) }}" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Tax TIN Identifier</label>
                <input type="text" name="tax_identifier" value="{{ old('tax_identifier', $company->tax_identifier) }}" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $company->email) }}" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Status *</label>
                <select name="status" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                    <option value="ACTIVE" {{ $company->status === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                    <option value="INACTIVE" {{ $company->status === 'INACTIVE' ? 'selected' : '' }}>INACTIVE</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Official Address</label>
            <textarea name="address" rows="3" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">{{ old('address', $company->address) }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <a href="{{ route('admin.companies.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Update Company</button>
        </div>
    </form>
</div>
@endsection
