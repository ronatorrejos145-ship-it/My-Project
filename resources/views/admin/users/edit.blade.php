@extends('layouts.app')

@section('title', 'Edit User')
@section('header', 'Edit User: ' . $user->name)

@section('content')
<div class="max-w-2xl bg-white p-6 rounded shadow-sm border border-gray-200">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Password (leave blank to keep current)</label>
            <input type="password" name="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
                @foreach($statuses as $st)
                <option value="{{ $st->value }}" {{ ($user->status instanceof App\Enums\UserStatus ? $user->status->value : $user->status) === $st->value ? 'selected' : '' }}>{{ $st->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Assign Roles</label>
            <div class="mt-2 space-y-2">
                @foreach($roles as $role)
                <label class="inline-flex items-center mr-4">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" {{ $user->roles->contains($role->id) ? 'checked' : '' }} class="rounded border-gray-300 text-sky-600">
                    <span class="ml-2 text-sm text-gray-700">{{ $role->name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-gray-300 rounded text-sm text-gray-700">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-sky-600 text-white rounded text-sm font-medium">Update User</button>
        </div>
    </form>
</div>
@endsection
