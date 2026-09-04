@extends('layouts.app')

@section('title', 'Add Branch - Master Data')

@section('content')
<div class="p-6 max-w-3xl">
    <div class="pb-6 border-b border-slate-200 dark:border-slate-800">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Create New Branch</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Add a branch office or service center.</p>
    </div>

    <form method="POST" action="{{ route('admin.branches.store') }}" class="mt-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Company *</label>
                <select name="company_id" required class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->legal_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Branch Code *</label>
                <input type="text" name="code" value="{{ old('code') }}" required class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                @error('code') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Branch Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Branch Type *</label>
                <select name="branch_type" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                    <option value="HEAD_OFFICE">HEAD_OFFICE</option>
                    <option value="BRANCH" selected>BRANCH</option>
                    <option value="SERVICE_CENTER">SERVICE_CENTER</option>
                    <option value="WAREHOUSE">WAREHOUSE</option>
                    <option value="TECHNICAL_HUB">TECHNICAL_HUB</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Latitude (GPS)</label>
                <input type="text" name="latitude" value="{{ old('latitude') }}" placeholder="14.6507000" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-mono">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Longitude (GPS)</label>
                <input type="text" name="longitude" value="{{ old('longitude') }}" placeholder="121.0300000" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-mono">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Status *</label>
                <select name="status" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                    <option value="ACTIVE">ACTIVE</option>
                    <option value="INACTIVE">INACTIVE</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Address</label>
            <textarea name="address" rows="3" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">{{ old('address') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <a href="{{ route('admin.branches.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Save Branch</button>
        </div>
    </form>
</div>
@endsection
