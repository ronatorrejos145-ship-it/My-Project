<?php

namespace App\Services;

use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PackageVersionService
{
    /**
     * Create a new package version, auto-incrementing version number and closing out the previous active version.
     */
    public function createVersion(ServicePackage $package, array $data): ServicePackageVersion
    {
        return DB::transaction(function () use ($package, $data) {
            $latestVersion = ServicePackageVersion::where('package_id', $package->id)
                ->orderBy('version_number', 'desc')
                ->first();

            $nextVersionNum = $latestVersion ? ($latestVersion->version_number + 1) : 1;
            $effectiveFrom = $data['effective_from'] ?? now();

            // Close out previous version if effective date has arrived
            if ($latestVersion && $latestVersion->status === 'ACTIVE') {
                $latestVersion->update([
                    'effective_until' => $effectiveFrom,
                ]);
            }

            $version = ServicePackageVersion::create([
                'package_id' => $package->id,
                'version_number' => $nextVersionNum,
                'version_name' => $data['version_name'] ?? "Version {$nextVersionNum}",
                'effective_from' => $effectiveFrom,
                'effective_until' => $data['effective_until'] ?? null,
                'price' => $data['price'],
                'installation_fee' => $data['installation_fee'] ?? 0.00,
                'activation_fee' => $data['activation_fee'] ?? 0.00,
                'deposit_amount' => $data['deposit_amount'] ?? 0.00,
                'reconnection_fee' => $data['reconnection_fee'] ?? 0.00,
                'relocation_fee' => $data['relocation_fee'] ?? 0.00,
                'equipment_fee' => $data['equipment_fee'] ?? 0.00,
                'download_speed' => $data['download_speed'] ?? $package->download_speed,
                'upload_speed' => $data['upload_speed'] ?? $package->upload_speed,
                'guaranteed_speed' => $data['guaranteed_speed'] ?? $package->speed_guaranteed,
                'speed_unit' => $data['speed_unit'] ?? $package->speed_unit,
                'billing_cycle_id' => $data['billing_cycle_id'] ?? $package->billing_cycle_id,
                'contract_period_months' => $data['contract_period_months'] ?? $package->contract_period_months,
                'grace_period_days' => $data['grace_period_days'] ?? $package->grace_period_days,
                'status' => 'ACTIVE',
                'change_reason' => $data['change_reason'] ?? 'Price or term update',
                'created_by' => Auth::id(),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Sync base package pricing summary fields for active catalog display
            $package->update([
                'base_price' => $version->price,
                'installation_fee' => $version->installation_fee,
                'activation_fee' => $version->activation_fee,
                'deposit_amount' => $version->deposit_amount,
                'download_speed' => $version->download_speed,
                'upload_speed' => $version->upload_speed,
            ]);

            return $version;
        });
    }

    /**
     * Generate diff array between two package versions.
     */
    public function compareVersions(ServicePackageVersion $v1, ServicePackageVersion $v2): array
    {
        $fields = [
            'price' => 'Monthly Price',
            'installation_fee' => 'Installation Fee',
            'activation_fee' => 'Activation Fee',
            'deposit_amount' => 'Deposit Amount',
            'download_speed' => 'Download Speed',
            'upload_speed' => 'Upload Speed',
            'contract_period_months' => 'Contract Months',
            'grace_period_days' => 'Grace Period Days',
        ];

        $diffs = [];
        foreach ($fields as $field => $label) {
            $val1 = $v1->$field;
            $val2 = $v2->$field;

            if ($val1 != $val2) {
                $diffs[] = [
                    'field' => $label,
                    'old_value' => $val1,
                    'new_value' => $val2,
                ];
            }
        }

        return $diffs;
    }
}
