@extends('layouts.app')

@section('title', 'Customer 360 - ' . $customer->full_name)

@section('content')
<div class="p-6">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $customer->full_name }}</h1>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                        {{ $customer->status }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                        {{ $customer->customer_type }}
                    </span>
                </div>
                <div class="mt-2 flex flex-wrap gap-4 text-xs font-mono text-slate-500 dark:text-slate-400">
                    <span>Customer #: <strong class="text-indigo-600 dark:text-indigo-400">{{ $customer->customer_number }}</strong></span>
                    <span>Account #: <strong class="text-indigo-600 dark:text-indigo-400">{{ $customer->account_number }}</strong></span>
                    <span>Branch: <strong>{{ $customer->branch->name ?? 'Head Office' }}</strong></span>
                    <span>Assigned Staff: <strong>{{ $customer->assignedEmployee->user->name ?? 'Unassigned' }}</strong></span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.customers.edit', $customer) }}" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-medium hover:bg-slate-100">
                    Edit Profile
                </a>
                <button onclick="document.getElementById('statusModal').classList.remove('hidden')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium">
                    Transition Status
                </button>
            </div>
        </div>

        <!-- Metric Highlights -->
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-xs text-slate-400">Primary Phone</span>
                <p class="font-bold text-slate-800 dark:text-white">{{ $customer->primary_phone }}</p>
            </div>
            <div>
                <span class="text-xs text-slate-400">Email</span>
                <p class="font-bold text-slate-800 dark:text-white">{{ $customer->email ?? '—' }}</p>
            </div>
            <div>
                <span class="text-xs text-slate-400">Current Balance</span>
                <p class="font-bold font-mono text-emerald-600 dark:text-emerald-400">₱{{ number_format($customer->current_balance, 2) }}</p>
            </div>
            <div>
                <span class="text-xs text-slate-400">Acquisition Source</span>
                <p class="font-bold text-slate-800 dark:text-white">{{ $customer->acquisition_source }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Customer 360 Detail Sections -->
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content Column -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Secure Document Management -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5">
                <div class="flex justify-between items-center pb-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-slate-800 dark:text-white text-base">📄 Customer Identification & Documents</h3>
                    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium">
                        + Upload Document
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($customer->documents as $doc)
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <div>
                                <span class="px-2 py-0.5 text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 rounded">{{ $doc->document_type }}</span>
                                <div class="font-semibold text-slate-800 dark:text-white text-sm mt-1">{{ $doc->original_filename }}</div>
                                <div class="text-xs text-slate-400">Uploaded: {{ $doc->uploaded_at?->format('M d, Y') }} • Size: {{ round($doc->file_size / 1024, 1) }} KB</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $doc->verification_status === 'VERIFIED' ? 'bg-emerald-100 text-emerald-800' : ($doc->verification_status === 'REJECTED' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $doc->verification_status }}
                                </span>
                                <a href="{{ route('admin.customers.documents.download', $doc) }}" class="px-2.5 py-1 bg-slate-800 text-white text-xs rounded hover:bg-slate-700">
                                    Download
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4">No documents uploaded yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- CRM Activity Timeline -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-800 dark:text-white text-base pb-4 border-b border-slate-200 dark:border-slate-800">⏱️ Customer 360 Activity Timeline</h3>
                <div class="mt-4 space-y-4">
                    @forelse($customer->activities as $act)
                        <div class="relative pl-6 border-l-2 border-indigo-500">
                            <div class="absolute -left-1.5 top-1 w-3 h-3 bg-indigo-600 rounded-full"></div>
                            <div class="font-bold text-slate-800 dark:text-white text-sm">{{ $act->title }}</div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $act->description }}</p>
                            <div class="text-xs text-slate-400 mt-1 font-mono">{{ $act->recorded_at?->format('M d, Y H:i A') }} • {{ $act->performer->name ?? 'System' }}</div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4">No activities logged yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Sidebar Column -->
        <div class="space-y-6">
            <!-- Internal Notes -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-800 dark:text-white text-base pb-3 border-b border-slate-200 dark:border-slate-800">📝 Internal CRM Notes</h3>

                <form method="POST" action="{{ route('admin.customers.notes.store', $customer) }}" class="mt-3 space-y-3">
                    @csrf
                    <textarea name="note" rows="2" required placeholder="Add private internal note..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs"></textarea>
                    <div class="flex justify-between items-center">
                        <select name="visibility" class="px-2 py-1 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-xs">
                            <option value="INTERNAL">INTERNAL</option>
                            <option value="TECHNICAL">TECHNICAL</option>
                            <option value="FINANCE">FINANCE</option>
                        </select>
                        <button type="submit" class="px-3 py-1 bg-slate-800 text-white rounded text-xs font-semibold">Post Note</button>
                    </div>
                </form>

                <div class="mt-4 space-y-3 max-h-80 overflow-y-auto">
                    @foreach($customer->notes as $note)
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-800 text-xs">
                            <p class="text-slate-700 dark:text-slate-300">{{ $note->note }}</p>
                            <div class="mt-2 text-slate-400 text-[10px] flex justify-between">
                                <span>{{ $note->creator->name ?? 'Staff' }}</span>
                                <span>{{ $note->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Transition Status -->
<div id="statusModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Transition Customer Status</h3>
        <form method="POST" action="{{ route('admin.customers.status', $customer) }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">New Status *</label>
                <select name="status" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                    <option value="PROSPECT">PROSPECT</option>
                    <option value="VERIFIED">VERIFIED</option>
                    <option value="ACTIVE">ACTIVE</option>
                    <option value="SUSPENDED">SUSPENDED</option>
                    <option value="TERMINATED">TERMINATED</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Reason *</label>
                <input type="text" name="reason" required placeholder="e.g. Identity verified, Payment confirmed" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
            </div>

            <div class="flex justify-end gap-2 pt-3">
                <button type="button" onclick="document.getElementById('statusModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg text-xs">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold">Save Status</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Upload Document -->
<div id="uploadModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Upload Customer Document</h3>
        <form method="POST" action="{{ route('admin.customers.documents.store', $customer) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Document Type *</label>
                <select name="document_type" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                    <option value="VALID_ID">VALID_ID</option>
                    <option value="PROOF_OF_ADDRESS">PROOF_OF_ADDRESS</option>
                    <option value="BUSINESS_REG">BUSINESS_REG</option>
                    <option value="CONTRACT">CONTRACT</option>
                    <option value="APPLICATION">APPLICATION</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Select File (PDF, PNG, JPG) *</label>
                <input type="file" name="document_file" required class="mt-1 w-full text-xs">
            </div>

            <div class="flex justify-end gap-2 pt-3">
                <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg text-xs">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold">Upload Document</button>
            </div>
        </form>
    </div>
</div>
@endsection
