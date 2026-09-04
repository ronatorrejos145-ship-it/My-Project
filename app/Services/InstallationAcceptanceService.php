<?php

namespace App\Services;

use App\Models\InstallationAcceptance;
use App\Models\InstallationStatusHistory;
use App\Models\InstallationWorkOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InstallationAcceptanceService
{
    public function recordAcceptance(
        InstallationWorkOrder $workOrder,
        string $signerName,
        string $signerRelationship = 'OWNER',
        string $status = 'ACCEPTED',
        ?string $rejectionReason = null,
        ?string $signaturePath = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $notes = null,
        ?int $userId = null
    ): InstallationAcceptance {
        return DB::transaction(function () use ($workOrder, $signerName, $signerRelationship, $status, $rejectionReason, $signaturePath, $ipAddress, $userAgent, $notes, $userId) {
            $workOrder = InstallationWorkOrder::where('id', $workOrder->id)->lockForUpdate()->firstOrFail();

            if ($status === 'REJECTED' && empty($rejectionReason)) {
                throw new InvalidArgumentException("Rejection reason is required when acceptance status is REJECTED.");
            }

            $acceptance = InstallationAcceptance::create([
                'installation_id' => $workOrder->id,
                'customer_id' => $workOrder->customer_id,
                'signer_name' => $signerName,
                'signer_relationship' => $signerRelationship,
                'acceptance_status' => $status,
                'rejection_reason' => $rejectionReason,
                'signature_path' => $signaturePath,
                'signed_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'notes' => $notes,
            ]);

            $oldStatus = $workOrder->status;
            if ($status === 'ACCEPTED' || $status === 'ACCEPTED_WITH_ISSUES') {
                $workOrder->update([
                    'accepted_at' => now(),
                    'status' => 'PENDING_ACCEPTANCE',
                    'updated_by' => $userId,
                ]);

                InstallationStatusHistory::create([
                    'installation_id' => $workOrder->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'PENDING_ACCEPTANCE',
                    'changed_by' => $userId,
                    'reason' => "Customer accepted installation ({$status})",
                ]);
            } elseif ($status === 'REJECTED') {
                $workOrder->update([
                    'status' => 'FAILED',
                    'failed_at' => now(),
                    'failure_reason' => 'Customer rejected installation: ' . $rejectionReason,
                    'updated_by' => $userId,
                ]);

                InstallationStatusHistory::create([
                    'installation_id' => $workOrder->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'FAILED',
                    'changed_by' => $userId,
                    'reason' => 'Customer rejected installation: ' . $rejectionReason,
                ]);
            }

            AuditLogService::log(
                'RECORD_CUSTOMER_ACCEPTANCE',
                'installations',
                $acceptance,
                null,
                $acceptance->toArray()
            );

            return $acceptance;
        });
    }
}
