<?php

namespace App\Services;

use App\Models\ServiceApplication;
use App\Models\ServiceApplicationStatusHistory;
use App\Models\Address;
use App\Models\ServicePackage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ServiceApplicationService
{
    protected NumberSequenceService $sequenceService;
    protected ServiceabilityEngineService $serviceabilityEngine;
    protected CustomerActivityService $activityService;

    public function __construct(
        NumberSequenceService $sequenceService,
        ServiceabilityEngineService $serviceabilityEngine,
        CustomerActivityService $activityService
    ) {
        $this->sequenceService = $sequenceService;
        $this->serviceabilityEngine = $serviceabilityEngine;
        $this->activityService = $activityService;
    }

    /**
     * Submit an online service application with address creation and serviceability check.
     */
    public function submitApplication(array $data): ServiceApplication
    {
        return DB::transaction(function () use ($data) {
            $applicationNumber = $this->sequenceService->getNextNumber('APPLICATION');

            // 1. Create Address Record
            $address = Address::create([
                'address_type' => 'INSTALLATION',
                'address_line_1' => $data['installation_address'] ?? 'Installation Address',
                'barangay_id' => $data['barangay_id'] ?? null,
                'city_municipality_id' => $data['city_municipality_id'] ?? null,
                'province_id' => $data['province_id'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'coordinate_accuracy' => $data['gps_accuracy'] ?? null,
                'landmark' => $data['landmark'] ?? null,
            ]);

            $package = ServicePackage::findOrFail($data['service_package_id']);
            $activeVersion = $package->activeVersion ?: $package->versions->first();

            // 2. Create Service Application Record
            $application = ServiceApplication::create([
                'application_number' => $applicationNumber,
                'customer_id' => $data['customer_id'] ?? null,
                'lead_id' => $data['lead_id'] ?? null,
                'applicant_type' => $data['applicant_type'] ?? 'INDIVIDUAL',
                'first_name' => $data['first_name'] ?? null,
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'business_name' => $data['business_name'] ?? null,
                'primary_phone' => $data['primary_phone'],
                'email' => $data['email'] ?? null,
                'service_package_id' => $package->id,
                'service_package_version_id' => $activeVersion?->id,
                'branch_id' => $data['branch_id'] ?? null,
                'service_area_id' => $data['service_area_id'] ?? null,
                'installation_address_id' => $address->id,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'gps_accuracy' => $data['gps_accuracy'] ?? null,
                'location_source' => $data['location_source'] ?? 'MAP_PIN',
                'status' => $data['status'] ?? 'SUBMITTED',
                'application_source' => $data['application_source'] ?? 'ONLINE_PORTAL',
                'submitted_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            // 3. Run Serviceability Check if coordinates exist
            if ($data['latitude'] && $data['longitude']) {
                $check = $this->serviceabilityEngine->evaluate(
                    (float)$data['latitude'],
                    (float)$data['longitude'],
                    $package,
                    $data['service_area_id'] ?? null,
                    $application->id,
                    $data['customer_id'] ?? null
                );

                // Update application status if survey required or out of coverage
                if ($check->result_status === 'REQUIRES_TECHNICAL_SURVEY') {
                    $application->status = 'REQUIRES_SURVEY';
                    $application->save();
                }
            }

            // Log status history
            ServiceApplicationStatusHistory::create([
                'application_id' => $application->id,
                'previous_status' => null,
                'new_status' => $application->status,
                'reason' => 'Application submitted online.',
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);

            if ($application->customer_id) {
                $this->activityService->log(
                    $application->customer_id,
                    'APPLICATION_CREATED',
                    "Service Application #{$applicationNumber} Submitted",
                    "Service application for '{$package->name}' created.",
                    ['application_number' => $applicationNumber]
                );
            }

            return $application;
        });
    }
}
