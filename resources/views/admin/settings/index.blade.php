@extends('layouts.app')

@section('title', 'Settings')
@section('header', 'System Settings')

@section('content')
<div class="max-w-3xl bg-white p-6 rounded shadow-sm border border-gray-200">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach($settings as $group => $groupSettings)
        <div class="border-b pb-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 mb-3">{{ ucfirst($group) }} Configuration</h3>

            <div class="space-y-4">
                @foreach($groupSettings as $setting)
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $setting->description ?: $setting->key }}</label>
                    <input type="text" name="settings[{{ $setting->key }}]" value="{{ old('settings.' . $setting->key, $setting->value) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded shadow-sm">
                    <p class="text-xs text-gray-400 mt-0.5 font-mono">key: {{ $setting->key }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-sky-600 text-white rounded text-sm font-medium">Save Configuration</button>
        </div>
    </form>
</div>
@endsection
