@extends('layouts.app')

@section('title', 'Create Department')
@section('header', 'Create New Department')

@section('content')
<div class="max-w-2xl bg-white p-6 rounded shadow-sm border border-gray-200">
    <form method="POST" action="{{ route('admin.departments.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700">Department Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Department Code (e.g. NOC)</label>
            <input type="text" name="code" value="{{ old('code') }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="2" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
                <option value="ACTIVE">Active</option>
                <option value="INACTIVE">Inactive</option>
            </select>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <a href="{{ route('admin.departments.index') }}" class="px-4 py-2 border border-gray-300 rounded text-sm text-gray-700">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-sky-600 text-white rounded text-sm font-medium">Save Department</button>
        </div>
    </form>
</div>
@endsection
