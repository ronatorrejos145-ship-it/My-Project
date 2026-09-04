<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkOrderChecklistTemplate;
use App\Models\WorkOrderChecklistItem;
use App\Models\MaintenancePlan;
use App\Models\WorkOrder;
use App\Models\Customer;
use App\Models\User;
use App\Models\Item;
use App\Models\Tool;
use App\Models\Asset;
use App\Services\WorkOrderService;
use App\Services\DispatchService;
use App\Services\WorkOrderGpsService;
use App\Services\WorkOrderDiagnosticService;
use App\Services\WorkOrderMaterialService;
use App\Services\EquipmentReplacementService;
use App\Services\WorkOrderCompletionService;
use Illuminate\Support\Facades\DB;

class Phase19FieldServiceSeeder extends Seeder
{
    public function run(): void
    {
        $tech = User::firstOrCreate(
            ['email' => 'fieldtech@example.com'],
            [
                'name' => 'Field Technician Alex',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        $customer = Customer::first() ?? Customer::create([
            'customer_number' => 'CUST-1001',
            'account_number' => 'ACC-1001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'primary_phone' => '09171234567',
            'installation_address' => '123 Fiber St, Manila',
            'gps_latitude' => 14.5995,
            'gps_longitude' => 120.9842,
            'status' => 'ACTIVE',
        ]);

        $itemCategory = DB::table('item_categories')->first()?->id ?? DB::table('item_categories')->insertGetId([
            'code' => 'CAT-CAB',
            'name' => 'Cables & Wiring',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $item = Item::first() ?? Item::create([
            'sku' => 'FIBER-DROP-100M',
            'name' => 'Fiber Drop Cable 100m',
            'category_id' => $itemCategory,
            'unit_of_measure' => 'METER',
        ]);

        $toolCategory = DB::table('tool_categories')->first()?->id ?? DB::table('tool_categories')->insertGetId([
            'code' => 'CAT-TOOLS',
            'name' => 'Testing Tools',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tool = Tool::first() ?? Tool::create([
            'tool_code' => 'TL-OPT-METER',
            'name' => 'Optical Power Meter',
            'category_id' => $toolCategory,
            'status' => 'AVAILABLE',
        ]);

        $assetCategory = DB::table('asset_categories')->first()?->id ?? DB::table('asset_categories')->insertGetId([
            'code' => 'CAT-NET',
            'name' => 'Network Routers',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $asset = Asset::first() ?? Asset::create([
            'asset_tag' => 'AST-RTR-9999',
            'model_name' => 'Dual Band Wi-Fi 6 Router',
            'serial_number' => 'SN-9999-WIFI6',
            'mac_address' => 'AA:BB:CC:DD:EE:99',
            'asset_category_id' => $assetCategory,
            'current_status' => 'AVAILABLE',
        ]);

        // Create Checklist Template
        $template = WorkOrderChecklistTemplate::create([
            'name' => 'Standard Fiber Restoration Checklist',
            'work_order_type' => 'CORRECTIVE',
            'description' => 'Mandatory verification steps before closing fiber work orders.',
            'is_mandatory' => true,
        ]);

        WorkOrderChecklistItem::create([
            'template_id' => $template->id,
            'step_number' => 1,
            'item_label' => 'Inspect drop cable physical integrity',
            'item_type' => 'CHECKBOX',
            'is_required' => true,
        ]);

        WorkOrderChecklistItem::create([
            'template_id' => $template->id,
            'step_number' => 2,
            'item_label' => 'Verify RX Optical Power level (-15 to -25 dBm)',
            'item_type' => 'YES_NO',
            'is_required' => true,
        ]);

        // Create Maintenance Plan
        $plan = MaintenancePlan::create([
            'plan_code' => 'PLAN-FIBER-QUARTERLY',
            'name' => 'Quarterly Optical Node Inspection',
            'description' => 'Clean optical connectors and inspect cabinet power supply.',
            'maintenance_type' => 'PREVENTIVE',
            'frequency' => 'QUARTERLY',
            'estimated_duration_minutes' => 45,
            'checklist_template_id' => $template->id,
            'created_by_user_id' => $tech->id,
        ]);

        // Create Work Order
        $woService = app(WorkOrderService::class);
        $workOrder = $woService->createWorkOrder([
            'customer_id' => $customer->id,
            'work_order_type' => 'CORRECTIVE',
            'priority' => 'HIGH',
            'severity' => 'MAJOR',
            'title' => 'DEMO: Fiber Cut Repair & Router Swap',
            'description' => 'Customer reported complete fiber link down.',
            'service_address' => $customer->installation_address,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
        ], $tech->id);

        // Assign Technician
        $dispatchService = app(DispatchService::class);
        $dispatchService->assignTechnician($workOrder, $tech->id, $tech->id, 'Alpha Team', 'Assigned for priority repair');

        // GPS Travel & Arrival
        $gpsService = app(WorkOrderGpsService::class);
        $gpsService->recordGpsEvent($workOrder, $tech->id, 'TRAVEL_STARTED', 14.5995, 120.9842);
        $gpsService->recordGpsEvent($workOrder, $tech->id, 'ARRIVED', 14.5995, 120.9842);
        $gpsService->recordGpsEvent($workOrder, $tech->id, 'WORK_STARTED', 14.5995, 120.9842);

        // Record Diagnostics
        $diagService = app(WorkOrderDiagnosticService::class);
        $diagService->recordDiagnostic($workOrder, [
            'device_powered' => true,
            'wan_status' => 'CONNECTED',
            'rx_power_dbm' => -18.50,
            'download_speed_mbps' => 100.00,
            'upload_speed_mbps' => 100.00,
            'latency_ms' => 10.00,
            'diagnosis_notes' => 'Fiber drop cable re-spliced, signal restored to -18.5 dBm',
        ], $tech->id);

        // Consume Material
        $matService = app(WorkOrderMaterialService::class);
        $matService->consumeMaterial($workOrder, $item->id, 1.0, null, 'SN-CABLE-100M', $tech->id);

        // Equipment Swap
        $repService = app(EquipmentReplacementService::class);
        $repService->replaceEquipment($workOrder, null, 'SN-OLD-ROUTER', 'AA:BB:CC:DD:00:11', $asset->id, $asset->serial_number, $asset->mac_address, 'Upgraded to Wi-Fi 6 router', $tech->id);

        // Complete Job
        $compService = app(WorkOrderCompletionService::class);
        $compService->completeWorkOrder($workOrder, [
            'actual_root_cause' => 'Cable cut due to fallen tree branch',
            'corrective_action' => 'Re-spliced fiber line and replaced ONU router',
            'confirmed_by_name' => 'John Doe',
            'rating' => 5,
            'customer_comments' => 'Excellent fast restoration!',
            'outage_start_at' => now()->subHours(2)->toDateTimeString(),
            'outage_end_at' => now()->toDateTimeString(),
        ], $tech->id);
    }
}
