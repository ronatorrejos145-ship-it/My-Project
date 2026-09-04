<?php

namespace App\Services;

use App\Models\ServiceAccount;
use App\Models\ServiceRequest;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class ServiceRequestService
{
    public function __construct(protected NumberSequenceService $numberSequenceService) {}

    public function createServiceRequest(
        ServiceAccount $serviceAccount,
        string $requestType, // PACKAGE_UPGRADE, PACKAGE_DOWNGRADE, RELOCATION, SUSPENSION, RECONNECTION, TERMINATION, OWNERSHIP_TRANSFER
        array $payload = [],
        ?string $reason = null,
        ?int $userId = null
    ): ServiceRequest {
        return DB::transaction(function () use ($serviceAccount, $requestType, $payload, $reason, $userId) {
            $reqNumber = $this->numberSequenceService->getNextNumber('SERVICE_REQUEST');

            $sr = ServiceRequest::create([
                'request_number' => $reqNumber,
                'service_account_id' => $serviceAccount->id,
                'customer_id' => $serviceAccount->customer_id,
                'request_type' => $requestType,
                'priority' => 'NORMAL',
                'status' => 'SUBMITTED',
                'request_payload' => $payload,
                'reason' => $reason,
            ]);

            AuditLogService::log(
                'CREATE_SERVICE_REQUEST',
                'services',
                $sr,
                null,
                $sr->toArray()
            );

            return $sr;
        });
    }

    public function approveRequest(ServiceRequest $sr, ?int $userId = null): ServiceRequest
    {
        return DB::transaction(function () use ($sr, $userId) {
            $sr = ServiceRequest::where('id', $sr->id)->lockForUpdate()->firstOrFail();

            $sr->update([
                'status' => 'APPROVED',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            AuditLogService::log(
                'APPROVE_SERVICE_REQUEST',
                'services',
                $sr,
                ['status' => 'SUBMITTED'],
                ['status' => 'APPROVED']
            );

            return $sr;
        });
    }
}
