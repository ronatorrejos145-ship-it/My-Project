@extends('layouts.app')

@section('title', 'Configure Role')
@section('header', 'Configure Role: ' . $role->name)

@section('content')
<div class="max-w-4xl bg-white p-6 rounded shadow-sm border border-gray-200">
    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Role Name</label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Role Code</label>
                <input type="text" value="{{ $role->code }}" disabled class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded shadow-sm text-gray-500 font-mono">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="2" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">{{ old('description', $role->description) }}</textarea>
        </div>

        <div>
            <h3 class="text-sm font-bold text-gray-800 mb-3">Permissions Matrix</h3>
            <div class="space-y-4">
                @foreach($permissions as $module => $modulePermissions)
                <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">{{ ucfirst($module) }} Module</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($modulePermissions as $p)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="permissions[]" value="{{ $p->id }}" {{ $role->permissions->contains($p->id) ? 'checked' : '' }} class="rounded border-gray-300 text-sky-600">
                            <span class="ml-2 text-xs text-gray-700">{{ $p->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t">
            <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 border border-gray-300 rounded text-sm text-gray-700">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-sky-600 text-white rounded text-sm font-medium">Update Role Permissions</button>
        </div>
    </form>
</div>
@endsection
