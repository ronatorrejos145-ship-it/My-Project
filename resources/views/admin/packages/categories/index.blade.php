@extends('layouts.app')

@section('title', 'Service Categories - Catalog')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Service Categories</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Broadband service classification master records.</p>
        </div>
        <div>
            <button onclick="document.getElementById('catModal').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium text-sm transition">
                + Add Category
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                        <th class="p-4">Code</th>
                        <th class="p-4">Category Name</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Plans Count</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($categories as $cat)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-medium text-indigo-600 dark:text-indigo-400">{{ $cat->code }}</td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-white">{{ $cat->name }}</td>
                            <td class="p-4"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-800">{{ $cat->category_type }}</span></td>
                            <td class="p-4 font-bold">{{ $cat->packages_count }} Plans</td>
                            <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">{{ $cat->status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500 dark:text-slate-400">No service categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $categories->links() }}
        </div>
    </div>
</div>

<!-- Modal: Add Category -->
<div id="catModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Create Service Category</h3>
        <form method="POST" action="{{ route('admin.packages.categories.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Category Code *</label>
                <input type="text" name="code" required placeholder="e.g. CAT-HOME" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Category Name *</label>
                <input type="text" name="name" required placeholder="e.g. Home Fiber Internet" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Category Type *</label>
                <select name="category_type" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                    <option value="HOME">HOME</option>
                    <option value="BUSINESS">BUSINESS</option>
                    <option value="CORPORATE">CORPORATE</option>
                    <option value="PREPAID">PREPAID</option>
                </select>
            </div>

            <input type="hidden" name="status" value="ACTIVE">

            <div class="flex justify-end gap-2 pt-3">
                <button type="button" onclick="document.getElementById('catModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg text-xs">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold">Save Category</button>
            </div>
        </form>
    </div>
</div>
@endsection
