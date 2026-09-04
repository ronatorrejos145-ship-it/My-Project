<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Http\Requests\UploadCustomerDocumentRequest;
use App\Services\CustomerDocumentService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CustomerDocumentController extends Controller
{
    protected CustomerDocumentService $documentService;
    protected AuditLogService $auditLogService;

    public function __construct(CustomerDocumentService $documentService, AuditLogService $auditLogService)
    {
        $this->documentService = $documentService;
        $this->auditLogService = $auditLogService;
    }

    public function store(UploadCustomerDocumentRequest $request, Customer $customer)
    {
        $file = $request->file('document_file');

        $doc = $this->documentService->storeDocument(
            $customer->id,
            $file,
            $request->document_type,
            $request->document_number,
            $request->expiration_date,
            $request->notes
        );

        $this->auditLogService->log(
            'CUSTOMER_DOCUMENT_UPLOAD',
            'CustomerDocuments',
            $doc->id,
            null,
            $doc->toArray()
        );

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Customer document uploaded securely for verification.');
    }

    public function download(CustomerDocument $document)
    {
        Gate::authorize('view', $document);

        if (!Storage::exists($document->storage_path)) {
            abort(404, 'Document file not found.');
        }

        $this->auditLogService->log(
            'CUSTOMER_DOCUMENT_ACCESS',
            'CustomerDocuments',
            $document->id,
            null,
            ['filename' => $document->original_filename]
        );

        return Storage::download($document->storage_path, $document->original_filename);
    }

    public function verify(Request $request, CustomerDocument $document)
    {
        Gate::authorize('verify', $document);

        $request->validate([
            'verification_status' => 'required|in:VERIFIED,REJECTED',
            'rejection_reason' => 'required_if:verification_status,REJECTED|nullable|string|max:500',
        ]);

        $this->documentService->updateVerificationStatus(
            $document,
            $request->verification_status,
            $request->rejection_reason
        );

        return redirect()->route('admin.customers.show', $document->customer_id)
            ->with('success', "Document status updated to {$request->verification_status}.");
    }
}
