<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetStatusHistory;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetAssignmentService
{
    public function assignToCustomer(Asset $asset, Customer $customer, ?string $reason = null, ?int $userId = null): AssetAssignment
    {
        return DB::transaction(function () use ($asset, $customer, $reason, $userId) {
            /** @var Asset $asset */
            $asset = Asset::where('id', $asset->id)->lockForUpdate()->firstOrFail();

            if (in_array($asset->current_status, ['DISPOSED', 'RETIRED', 'STOLEN'])) {
                throw new InvalidArgumentException("Cannot assign asset {$asset->asset_tag} with status {$asset->current_status}.");
            }

            // Check if already assigned to another active customer
            if ($asset->assigned_customer_id && $asset->assigned_customer_id !== $customer->id && $asset->current_status === 'INSTALLED') {
                throw new InvalidArgumentException("Asset {$asset->asset_tag} is already assigned to Customer ID {$asset->assigned_customer_id}.");
            }

            // Close existing open assignment
            AssetAssignment::where('asset_id', $asset->id)->whereNull('returned_at')->update(['returned_at' => now()]);

            $assignment = AssetAssignment::create([
                'asset_id' => $asset->id,
                'assigned_to_type' => Customer::class,
                'assigned_to_id' => $customer->id,
                'assigned_by' => $userId,
                'assigned_at' => now(),
                'reason' => $reason ?? 'Customer equipment installation assignment',
            ]);

            $oldStatus = $asset->current_status;
            $oldLocation = $asset->current_location;

            $location = $customer->primaryAddress?->full_address ?? "Customer Account: {$customer->customer_number}";

            $asset->update([
                'current_status' => 'INSTALLED',
                'assigned_customer_id' => $customer->id,
                'assigned_employee_id' => null,
                'current_location' => $location,
            ]);

            AssetStatusHistory::create([
                'asset_id' => $asset->id,
                'old_status' => $oldStatus,
                'new_status' => 'INSTALLED',
                'old_location' => $oldLocation,
                'new_location' => $location,
                'changed_by' => $userId,
                'reason' => "Assigned to customer {$customer->customer_number}",
            ]);

            AuditLogService::log(
                'ASSIGN_ASSET_CUSTOMER',
                'assets',
                $asset,
                ['status' => $oldStatus, 'customer_id' => $asset->getOriginal('assigned_customer_id')],
                ['status' => 'INSTALLED', 'customer_id' => $customer->id]
            );

            return $assignment;
        });
    }

    public function assignToEmployee(Asset $asset, Employee $employee, ?string $reason = null, ?int $userId = null): AssetAssignment
    {
        return DB::transaction(function () use ($asset, $employee, $reason, $userId) {
            $asset = Asset::where('id', $asset->id)->lockForUpdate()->firstOrFail();

            if (in_array($asset->current_status, ['DISPOSED', 'RETIRED', 'STOLEN'])) {
                throw new InvalidArgumentException("Cannot assign asset {$asset->asset_tag} with status {$asset->current_status}.");
            }

            AssetAssignment::where('asset_id', $asset->id)->whereNull('returned_at')->update(['returned_at' => now()]);

            $assignment = AssetAssignment::create([
                'asset_id' => $asset->id,
                'assigned_to_type' => Employee::class,
                'assigned_to_id' => $employee->id,
                'assigned_by' => $userId,
                'assigned_at' => now(),
                'reason' => $reason ?? 'Field technician equipment assignment',
            ]);

            $oldStatus = $asset->current_status;
            $location = "Technician: {$employee->first_name} {$employee->last_name} ({$employee->employee_number})";

            $asset->update([
                'current_status' => 'ASSIGNED',
                'assigned_employee_id' => $employee->id,
                'assigned_customer_id' => null,
                'current_location' => $location,
            ]);

            AssetStatusHistory::create([
                'asset_id' => $asset->id,
                'old_status' => $oldStatus,
                'new_status' => 'ASSIGNED',
                'changed_by' => $userId,
                'reason' => "Assigned to employee {$employee->employee_number}",
            ]);

            AuditLogService::log(
                'ASSIGN_ASSET_EMPLOYEE',
                'assets',
                $asset,
                ['status' => $oldStatus],
                ['status' => 'ASSIGNED', 'employee_id' => $employee->id]
            );

            return $assignment;
        });
    }
}
