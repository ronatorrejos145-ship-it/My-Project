<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\InstallationWorkOrder;
use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Models\TechnicalSurvey;
use App\Services\NumberSequenceService;
use Illuminate\Database\Seeder;

class InstallationWorkOrderSeeder extends Seeder
{
    public function run(): void
    {
        $surveys = TechnicalSurvey::where('approval_status', 'APPROVED')->get();
        if ($surveys->isEmpty()) {
            return;
        }

        $numSeq = app(NumberSequenceService::class);

        foreach ($surveys as $index => $survey) {
            $woNum = $numSeq->generateNextNumber('INSTALLATION');

            $status = match ($index % 4) {
                0 => 'PENDING',
                1 => 'ASSIGNED',
                2 => 'SCHEDULED',
                3 => 'COMPLETED',
            };

            $tech = Employee::where('employment_status', 'ACTIVE')->first();

            InstallationWorkOrder::firstOrCreate(
                ['work_order_number' => $woNum],
                [
                    'application_id' => $survey->application_id,
                    'customer_id' => $survey->customer_id,
                    'technical_survey_id' => $survey->id,
                    'package_id' => $survey->package_id,
                    'package_version_id' => $survey->package_version_id,
                    'branch_id' => $survey->application->branch_id ?? Branch::first()->id,
                    'service_area_id' => $survey->service_area_id,
                    'installation_address_id' => $survey->installation_address_id,
                    'installation_location_id' => $survey->installation_location_id,
                    'latitude' => $survey->latitude ?? 14.5995123,
                    'longitude' => $survey->longitude ?? 120.9842234,
                    'gps_accuracy' => 3.50,
                    'work_type' => 'NEW_INSTALLATION',
                    'priority' => 'NORMAL',
                    'requested_date' => now()->toDateString(),
                    'target_date' => now()->addDays(2)->toDateString(),
                    'assigned_technician_id' => $tech?->id,
                    'status' => $status,
                    'notes' => 'Seeded development installation work order.',
                ]
            );
        }
    }
}
