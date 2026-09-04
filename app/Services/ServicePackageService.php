<?php

namespace App\Services;

use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Models\PackageEquipmentRequirement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ServicePackageService
{
    protected PackageVersionService $versionService;

    public function __construct(PackageVersionService $versionService)
    {
        $this->versionService = $versionService;
    }

    /**
     * Create a new Service Package along with its initial Version 1 record.
     */
    public function createPackage(array $data, array $features = [], array $equipmentRequirements = [], array $branchIds = [], array $serviceAreaIds = []): ServicePackage
    {
        return DB::transaction(function () use ($data, $features, $equipmentRequirements, $branchIds, $serviceAreaIds) {
            $data['status'] = $data['status'] ?? 'ACTIVE';
            $data['approval_status'] = $data['approval_status'] ?? 'APPROVED';

            $package = ServicePackage::create($data);

            // Create Version 1
            $this->versionService->createVersion($package, [
                'version_name' => 'Initial Launch Version',
                'effective_from' => now(),
                'price' => $package->base_price,
                'installation_fee' => $package->installation_fee,
                'activation_fee' => $package->activation_fee,
                'deposit_amount' => $package->deposit_amount,
                'reconnection_fee' => $package->reconnection_fee,
                'relocation_fee' => $package->relocation_fee,
                'download_speed' => $package->download_speed,
                'upload_speed' => $package->upload_speed,
                'guaranteed_speed' => $package->speed_guaranteed,
                'speed_unit' => $package->speed_unit,
                'billing_cycle_id' => $package->billing_cycle_id,
                'contract_period_months' => $package->contract_period_months,
                'grace_period_days' => $package->grace_period_days,
                'change_reason' => 'Initial Package Release',
            ]);

            // Sync features
            if (!empty($features)) {
                $package->features()->sync($features);
            }

            // Sync availability
            if (!empty($branchIds)) {
                $package->branches()->sync($branchIds);
            }

            if (!empty($serviceAreaIds)) {
                $package->serviceAreas()->sync($serviceAreaIds);
            }

            // Create equipment requirements
            foreach ($equipmentRequirements as $eq) {
                PackageEquipmentRequirement::create([
                    'package_id' => $package->id,
                    'asset_model_id' => $eq['asset_model_id'],
                    'quantity' => $eq['quantity'] ?? 1,
                    'is_required' => $eq['is_required'] ?? true,
                    'is_included' => $eq['is_included'] ?? true,
                    'notes' => $eq['notes'] ?? null,
                ]);
            }

            return $package;
        });
    }
}
