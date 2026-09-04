<?php

namespace App\Services;

use App\Models\CustomerDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomerDocumentService
{
    protected CustomerActivityService $activityService;

    public function __construct(CustomerActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Upload and store a customer document in private storage.
     */
    public function storeDocument(int $customerId, UploadedFile $file, string $documentType, ?string $docNumber = null, ?string $expirationDate = null, ?string $notes = null): CustomerDocument
    {
        $originalFilename = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $fileSize = $file->getSize();

        // Calculate SHA256 checksum for file integrity
        $checksum = hash_file('sha256', $file->getRealPath());

        // Store file in private disk storage (e.g., storage/app/private/customer_documents/{customer_id})
        $storedPath = $file->store("private/customer_documents/{$customerId}");

        $doc = CustomerDocument::create([
            'customer_id' => $customerId,
            'document_type' => $documentType,
            'document_number' => $docNumber,
            'original_filename' => $originalFilename,
            'storage_path' => $storedPath,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'checksum' => $checksum,
            'uploaded_by' => Auth::id(),
            'uploaded_at' => now(),
            'expiration_date' => $expirationDate,
            'verification_status' => 'PENDING',
            'notes' => $notes,
        ]);

        $this->activityService->log(
            $customerId,
            'DOCUMENT_UPLOADED',
            "Uploaded document: {$originalFilename}",
            "Document type '{$documentType}' uploaded for verification.",
            ['document_id' => $doc->id, 'document_type' => $documentType]
        );

        return $doc;
    }

    /**
     * Verify or reject a customer document.
     */
    public function updateVerificationStatus(CustomerDocument $document, string $status, ?string $rejectionReason = null): CustomerDocument
    {
        $document->verification_status = $status;
        $document->verified_by = Auth::id();
        $document->verified_at = now();

        if ($status === 'REJECTED') {
            $document->rejection_reason = $rejectionReason;
        }

        $document->save();

        $this->activityService->log(
            $document->customer_id,
            'DOCUMENT_VERIFIED',
            "Document {$status}: {$document->original_filename}",
            "Document verification status changed to {$status}." . ($rejectionReason ? " Reason: {$rejectionReason}" : ''),
            ['document_id' => $document->id, 'status' => $status]
        );

        return $document;
    }
}
