<?php

namespace App\Services;

use App\Models\InstallationStatusHistory;
use App\Models\InstallationWorkOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InstallationDispatchService
{
    public function dispatchEnRoute(InstallationWorkOrder $workOrder, ?int $userId = null): InstallationWorkOrder
    {
        return DB::transaction(function () use ($workOrder, $userId) {
            $workOrder = InstallationWorkOrder::where('id', $workOrder->id)->lockForUpdate()->firstOrFail();

            if (!in_array($workOrder->status, ['ASSIGNED', 'SCHEDULED', 'PENDING'])) {
                throw new InvalidArgumentException("Cannot set EN_ROUTE for installation with status {$workOrder->status}.");
            }

            $oldStatus = $workOrder->status;
            $workOrder->update([
                'status' => 'EN_ROUTE',
                'updated_by' => $userId,
            ]);

            InstallationStatusHistory::create([
                'installation_id' => $workOrder->id,
                'old_status' => $oldStatus,
                'new_status' => 'EN_ROUTE',
                'changed_by' => $userId,
                'reason' => 'Technician en route to installation location',
            ]);

            AuditLogService::log(
                'DISPATCH_EN_ROUTE',
                'installations',
                $workOrder,
                ['status' => $oldStatus],
                ['status' => 'EN_ROUTE']
            );

            return $workOrder;
        });
    }
}
