@extends('layouts.app')

@section('title', 'Employees')
@section('header', 'Employee Directory')

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search employee #, position, name..." class="px-3 py-1.5 border border-gray-300 rounded text-sm">
            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded text-sm">Filter</button>
        </form>

        @can('create', App\Models\Employee::class)
        <a href="{{ route('admin.employees.create') }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded text-sm">
            Add Employee
        </a>
        @endcan
    </div>

    <div class="bg-white shadow-sm rounded border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Emp #</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Department</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Position</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($employees as $emp)
                <tr>
                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-800">{{ $emp->employee_number }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $emp->user->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $emp->department->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $emp->position }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded bg-emerald-100 text-emerald-800">
                            {{ $emp->employment_status instanceof App\Enums\EmployeeStatus ? $emp->employment_status->value : $emp->employment_status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @can('update', $emp)
                        <a href="{{ route('admin.employees.edit', $emp) }}" class="text-sky-600 hover:text-sky-900 text-xs font-semibold">Edit</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">No employees registered yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $employees->links() }}
    </div>
</div>
@endsection
