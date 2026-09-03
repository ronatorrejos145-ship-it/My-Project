<?php

namespace Database\Seeders;

use App\Models\TechnicalSurvey;
use App\Models\TechnicalSurveyStatusHistory;
use App\Models\TechnicalSurveyMeasurement;
use App\Models\TechnicalSurveyMaterial;
use App\Models\TechnicalSurveyEquipment;
use App\Models\ServiceApplication;
use App\Models\Customer;
use App\Models\ServicePackage;
use App\Models\Employee;
use App\Models\Item;
use App\Models\AssetModel;
use Illuminate\Database\Seeder;

class TechnicalSurveySeeder extends Seeder
{
    public function run(): void
    {
        $app = ServiceApplication::first();
        $customer = Customer::first();
        $package = ServicePackage::first();
        $technician = Employee::first();
        $itemCable = Item::first();
        $assetModel = AssetModel::first();

        // Survey 1: Approved & Feasible
        $survey1 = TechnicalSurvey::firstOrCreate(
            ['survey_number' => 'SUR-2025-000001'],
            [
                'application_id' => $app?->id,
                'customer_id' => $customer?->id,
                'package_id' => $package->id,
                'package_version_id' => $package->activeVersion?->id,
                'technician_id' => $technician?->id,
                'survey_type' => 'NEW_INSTALLATION',
                'status' => 'APPROVED',
                'priority' => 'MEDIUM',
                'scheduled_at' => now()->subHours(5),
                'started_at' => now()->subHours(4),
                'completed_at' => now()->subHours(3),
                'submitted_at' => now()->subHours(3),
                'reviewed_at' => now()->subHours(1),
                'approved_at' => now()->subHours(1),
                'arrival_latitude' => 14.6520000,
                'arrival_longitude' => 121.0320000,
                'arrival_gps_accuracy' => 4.5,
                'arrival_verification_status' => 'ARRIVED_AT_SITE',
                'arrival_distance_meters' => 18.5,
                'line_of_sight_status' => 'CLEAR',
                'installation_complexity' => 'NORMAL',
                'safety_assessment' => 'SAFE',
                'technical_recommendation' => 'RECOMMENDED',
                'final_decision' => 'TECHNICALLY_FEASIBLE',
                'technical_summary' => 'On-site technical evaluation confirmed clear optical power signal (-18.5 dBm) and safe roof access.',
            ]
        );

        TechnicalSurveyMeasurement::firstOrCreate(
            ['survey_id' => $survey1->id, 'measurement_type' => 'OPTICAL_POWER'],
            ['value' => -18.50, 'unit' => 'dBm', 'acceptance_status' => 'PASS', 'measurement_tool' => 'Optical Power Meter']
        );

        if ($itemCable) {
            TechnicalSurveyMaterial::firstOrCreate(
                ['survey_id' => $survey1->id, 'item_id' => $itemCable->id],
                ['item_name' => $itemCable->name, 'estimated_quantity' => 35.00, 'unit' => 'METERS', 'notes' => 'Outdoor drop cable path']
            );
        }

        if ($assetModel) {
            TechnicalSurveyEquipment::firstOrCreate(
                ['survey_id' => $survey1->id, 'asset_model_id' => $assetModel->id],
                ['quantity' => 1, 'is_required' => true, 'notes' => 'Dual Band GPON ONT']
            );
        }

        TechnicalSurveyStatusHistory::firstOrCreate(
            ['survey_id' => $survey1->id, 'new_status' => 'APPROVED'],
            ['reason' => 'Technical survey reviewed and approved by supervisor.', 'changed_at' => now()->subHours(1)]
        );
    }
}
