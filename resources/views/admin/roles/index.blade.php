@extends('layouts.app')

@section('title', 'Roles')
@section('header', 'Roles & Permissions')

@section('content')
<div class="space-y-4">
    <div class="flex justify-end">
        @can('create', App\Models\Role::class)
        <a href="{{ route('admin.roles.create') }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded text-sm">
            Add Role
        </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($roles as $r)
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start">
                    <h3 class="text-base font-bold text-gray-800">{{ $r->name }}</h3>
                    <span class="px-2 py-0.5 text-xs font-mono font-semibold bg-slate-100 text-slate-700 rounded">{{ $r->code }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $r->description ?: 'No description provided.' }}</p>
                <div class="mt-3 flex gap-4 text-xs text-gray-600">
                    <span><strong>{{ $r->users_count }}</strong> User(s)</span>
                    <span><strong>{{ $r->permissions_count }}</strong> Permission(s)</span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex justify-end">
                @can('update', $r)
                <a href="{{ route('admin.roles.edit', $r) }}" class="text-xs text-sky-600 font-semibold hover:underline">Configure Permissions</a>
                @endcan
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
