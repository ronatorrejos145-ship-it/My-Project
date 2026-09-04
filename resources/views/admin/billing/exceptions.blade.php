@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Billing Exceptions</h1>
            <p class="text-sm text-gray-600">Review calculation errors, holds & missing price profiles.</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Exception #</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Type / Severity</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Message</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($exceptions as $exc)
                    <tr>
                        <td class="px-6 py-4 font-bold text-red-600">{{ $exc->exception_number }}</td>
                        <td class="px-6 py-4 text-xs font-medium">{{ $exc->serviceAccount->account_number ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-xs">
                            <span class="font-bold text-gray-800">{{ $exc->type }}</span>
                            <span class="block text-red-500 font-semibold">{{ $exc->severity }}</span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-600">{{ $exc->message }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800">
                                {{ $exc->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No open billing exceptions.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">
            {{ $exceptions->links() }}
        </div>
    </div>
</div>
@endsection
