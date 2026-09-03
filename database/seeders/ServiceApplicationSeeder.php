<?php

namespace Database\Seeders;

use App\Models\ServiceApplication;
use App\Models\ServiceApplicationStatusHistory;
use App\Models\ServiceabilityCheck;
use App\Models\ServicePackage;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\ServiceArea;
use App\Models\NetworkNode;
use Illuminate\Database\Seeder;

class ServiceApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $package = ServicePackage::first();
        $customer = Customer::first();
        $branch = Branch::first();
        $serviceArea = ServiceArea::first();
        $node = NetworkNode::first();

        // Application 1: Serviceable & Submitted
        $app1 = ServiceApplication::firstOrCreate(
            ['application_number' => 'APP-2025-000001'],
            [
                'customer_id' => $customer?->id,
                'applicant_type' => 'INDIVIDUAL',
                'first_name' => 'Maria',
                'last_name' => 'Dela Cruz',
                'primary_phone' => '+63 917 123 4567',
                'email' => 'maria.delacruz@demo.local',
                'service_package_id' => $package->id,
                'service_package_version_id' => $package->activeVersion?->id,
                'branch_id' => $branch?->id,
                'service_area_id' => $serviceArea?->id,
                'latitude' => 14.6520000,
                'longitude' => 121.0320000,
                'gps_accuracy' => 5.0,
                'location_source' => 'MAP_PIN',
                'status' => 'SUBMITTED',
                'application_source' => 'ONLINE_PORTAL',
                'submitted_at' => now()->subHours(2),
            ]
        );

        ServiceabilityCheck::firstOrCreate(
            ['application_id' => $app1->id],
            [
                'package_id' => $package->id,
                'package_version_id' => $package->activeVersion?->id,
                'latitude' => 14.6520000,
                'longitude' => 121.0320000,
                'service_area_id' => $serviceArea?->id,
                'result_status' => 'SERVICEABLE',
                'reason_code' => 'FIBER_NODE_IN_RANGE',
                'explanation' => 'Location is within 250m of Fiber Node.',
                'nearest_node_id' => $node?->id,
                'calculated_distance_meters' => 250.00,
                'capacity_status' => 'CAPACITY_AVAILABLE',
                'checked_at' => now()->subHours(2),
                'engine_version' => '1.0.0',
            ]
        );

        ServiceApplicationStatusHistory::firstOrCreate(
            ['application_id' => $app1->id, 'new_status' => 'SUBMITTED'],
            ['reason' => 'Online customer application submitted successfully.', 'changed_at' => now()->subHours(2)]
        );

        // Application 2: Requires Technical Survey
        $app2 = ServiceApplication::firstOrCreate(
            ['application_number' => 'APP-2025-000002'],
            [
                'applicant_type' => 'BUSINESS',
                'business_name' => 'Penduko Enterprise [DEMO]',
                'first_name' => 'Pedro',
                'last_name' => 'Penduko',
                'primary_phone' => '+63 920 987 6543',
                'email' => 'pedro@penduko.demo',
                'service_package_id' => $package->id,
                'branch_id' => $branch?->id,
                'latitude' => 14.6650000,
                'longitude' => 121.0450000,
                'status' => 'REQUIRES_SURVEY',
                'application_source' => 'ONLINE_PORTAL',
                'submitted_at' => now()->subDay(),
            ]
        );

        ServiceabilityCheck::firstOrCreate(
            ['application_id' => $app2->id],
            [
                'package_id' => $package->id,
                'latitude' => 14.6650000,
                'longitude' => 121.0450000,
                'result_status' => 'REQUIRES_TECHNICAL_SURVEY',
                'reason_code' => 'LINE_OF_SIGHT_CHECK',
                'explanation' => 'Nearest node is 1800m away. On-site technical survey required to verify cable drop feasibility.',
                'nearest_node_id' => $node?->id,
                'calculated_distance_meters' => 1800.00,
                'capacity_status' => 'CAPACITY_UNKNOWN',
                'checked_at' => now()->subDay(),
                'engine_version' => '1.0.0',
            ]
        );
    }
}
