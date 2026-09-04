@extends('layouts.app')

@section('title', 'Edit Package - Catalog')

@section('content')
<div class="p-6 max-w-4xl">
    <div class="pb-6 border-b border-slate-200 dark:border-slate-800">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Package: {{ $package->name }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Update general plan metadata. Price changes should be published as a new Version.</p>
    </div>

    <form method="POST" action="{{ route('admin.packages.update', $package) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Package Code *</label>
                    <input type="text" name="package_code" value="{{ old('package_code', $package->package_code) }}" required class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Package Name *</label>
                    <input type="text" name="name" value="{{ old('name', $package->name) }}" required class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Package Type</label>
                    <select name="package_type" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="RESIDENTIAL" {{ $package->package_type === 'RESIDENTIAL' ? 'selected' : '' }}>RESIDENTIAL</option>
                        <option value="BUSINESS" {{ $package->package_type === 'BUSINESS' ? 'selected' : '' }}>BUSINESS</option>
                        <option value="CORPORATE" {{ $package->package_type === 'CORPORATE' ? 'selected' : '' }}>CORPORATE</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Technology</label>
                    <select name="technology" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="FIBER" {{ $package->technology === 'FIBER' ? 'selected' : '' }}>FIBER</option>
                        <option value="WIRELESS" {{ $package->technology === 'WIRELESS' ? 'selected' : '' }}>WIRELESS</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Status</label>
                    <select name="status" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="ACTIVE" {{ $package->status === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                        <option value="INACTIVE" {{ $package->status === 'INACTIVE' ? 'selected' : '' }}>INACTIVE</option>
                        <option value="DISCONTINUED" {{ $package->status === 'DISCONTINUED' ? 'selected' : '' }}>DISCONTINUED</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Description</label>
                <textarea name="description" rows="3" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">{{ old('description', $package->description) }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <a href="{{ route('admin.packages.show', $package) }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                Update Metadata
            </button>
        </div>
    </form>
</div>
@endsection
