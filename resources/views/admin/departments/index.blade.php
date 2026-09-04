@extends('layouts.app')

@section('title', 'Departments')
@section('header', 'Department Management')

@section('content')
<div class="space-y-4">
    <div class="flex justify-end">
        @can('create', App\Models\Department::class)
        <a href="{{ route('admin.departments.create') }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded text-sm">
            Add Department
        </a>
        @endcan
    </div>

    <div class="bg-white shadow-sm rounded border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Code</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Employees</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($departments as $dept)
                <tr>
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $dept->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $dept->code }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $dept->employees_count }} employee(s)</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $dept->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $dept->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @can('update', $dept)
                        <a href="{{ route('admin.departments.edit', $dept) }}" class="text-sky-600 hover:text-sky-900 text-xs font-semibold">Edit</a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
