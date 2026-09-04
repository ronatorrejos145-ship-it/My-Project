@extends('layouts.app')

@section('title', 'Register Customer - CRM')

@section('content')
<div class="p-6 max-w-4xl">
    <div class="pb-6 border-b border-slate-200 dark:border-slate-800">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Register New Customer</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Master subscriber profile creation with auto-generated account numbers.</p>
    </div>

    @if(session('warning'))
        <div class="mt-4 p-4 bg-amber-100 border border-amber-400 text-amber-800 rounded-lg text-sm font-medium">
            ⚠️ {{ session('warning') }}
        </div>
    @endif

    @if(isset($duplicates) && $duplicates->isNotEmpty())
        <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 rounded-xl">
            <h4 class="font-bold text-amber-800 dark:text-amber-300 text-sm">Potential Duplicate Accounts Detected:</h4>
            <div class="mt-2 divide-y divide-amber-200 dark:divide-amber-800 text-xs">
                @foreach($duplicates as $dup)
                    <div class="py-2 flex justify-between items-center">
                        <div>
                            <span class="font-bold">#{{ $dup->customer_number }}</span> - {{ $dup->full_name }} ({{ $dup->primary_phone }} / {{ $dup->email }})
                        </div>
                        <a href="{{ route('admin.customers.show', $dup) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">View Profile ↗</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.customers.store') }}" class="mt-6 space-y-6">
        @csrf

        @if(isset($duplicates) && $duplicates->isNotEmpty())
            <input type="hidden" name="confirm_duplicate" value="1">
        @endif

        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="font-bold text-slate-800 dark:text-white text-sm">1. Customer Identity & Classification</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Customer Type *</label>
                    <select name="customer_type" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="RESIDENTIAL">RESIDENTIAL</option>
                        <option value="INDIVIDUAL">INDIVIDUAL</option>
                        <option value="BUSINESS">BUSINESS</option>
                        <option value="CORPORATE">CORPORATE</option>
                        <option value="GOVERNMENT">GOVERNMENT</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Branch Location *</label>
                    <select name="branch_id" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Business / Company Name (If Applicable)</label>
                    <input type="text" name="business_name" value="{{ old('business_name') }}" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Primary Mobile Phone *</label>
                    <input type="text" name="primary_phone" value="{{ old('primary_phone') }}" required placeholder="+63 917 000 0000" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Acquisition Source</label>
                    <select name="acquisition_source" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                        <option value="WALK_IN">WALK_IN</option>
                        <option value="WEBSITE">WEBSITE</option>
                        <option value="FACEBOOK">FACEBOOK</option>
                        <option value="FIELD_SALES">FIELD_SALES</option>
                        <option value="REFERRAL">REFERRAL</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="font-bold text-slate-800 dark:text-white text-sm">2. Installation & Billing Addresses</h3>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Installation Address</label>
                <textarea name="installation_address" rows="2" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">{{ old('installation_address') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Billing Address</label>
                <textarea name="billing_address" rows="2" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">{{ old('billing_address') }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                {{ isset($duplicates) && $duplicates->isNotEmpty() ? 'Confirm & Create Customer' : 'Save Customer Account' }}
            </button>
        </div>
    </form>
</div>
@endsection
