@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Create Installation Work Order</h1>

        <form action="{{ route('admin.installations.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Approved Technical Survey *</label>
                <select name="technical_survey_id" class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">-- Select Approved Survey --</option>
                    @foreach($approvedSurveys as $survey)
                        <option value="{{ $survey->id }}">
                            {{ $survey->survey_number }} - {{ $survey->customer->full_name ?? ($survey->customer->first_name . ' ' . $survey->customer->last_name) }} ({{ $survey->package->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Work Type</label>
                    <select name="work_type" class="w-full border-gray-300 rounded shadow-sm">
                        <option value="NEW_INSTALLATION">NEW_INSTALLATION</option>
                        <option value="RELOCATION">RELOCATION</option>
                        <option value="PACKAGE_UPGRADE">PACKAGE_UPGRADE</option>
                        <option value="REINSTALLATION">REINSTALLATION</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select name="priority" class="w-full border-gray-300 rounded shadow-sm">
                        <option value="NORMAL">NORMAL</option>
                        <option value="HIGH">HIGH</option>
                        <option value="URGENT">URGENT</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Requested Date</label>
                    <input type="date" name="requested_date" value="{{ date('Y-m-d') }}" class="w-full border-gray-300 rounded shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Completion Date</label>
                    <input type="date" name="target_date" value="{{ date('Y-m-d', strtotime('+2 days')) }}" class="w-full border-gray-300 rounded shadow-sm">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Technical Notes</label>
                <textarea name="notes" rows="3" class="w-full border-gray-300 rounded shadow-sm" placeholder="Additional instructions for technicians..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.installations.index') }}" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">Create Work Order</button>
            </div>
        </form>
    </div>
</div>
@endsection
