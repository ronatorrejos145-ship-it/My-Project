<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\Item;
use App\Models\Asset;
use App\Models\WorkOrder;
use App\Models\MaintenancePlan;
use App\Models\MaintenancePlanSchedule;
use App\Models\WorkOrderChecklistTemplate;
use App\Services\WorkOrderService;
use App\Services\DispatchService;
use App\Services\WorkOrderGpsService;
use App\Services\WorkOrderChecklistService;
use App\Services\WorkOrderDiagnosticService;
use App\Services\WorkOrderMaterialService;
use App\Services\EquipmentReplacementService;
use App\Services\WorkOrderCompletionService;
use App\Services\PreventiveMaintenanceService;
use App\Services\WorkOrderReportService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Phase19FieldServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $technician;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Admin User']);
        $this->technician = User::factory()->create(['name' => 'Field Tech']);

        $this->customer = Customer::create([
            'customer_number' => 'CUST-8001',
            'account_number' => 'ACC-8001',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'primary_phone' => '09181234567',
            'installation_address' => '456 Fiber Ave, Quezon City',
            'gps_latitude' => 14.6507,
            'gps_longitude' => 121.0333,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_work_order_creation_and_numbering()
    {
        $woService = app(WorkOrderService::class);

        $wo = $woService->createWorkOrder([
            'customer_id' => $this->customer->id,
            'work_order_type' => 'CORRECTIVE',
            'priority' => 'HIGH',
            'title' => 'No Fiber Connection',
            'description' => 'LOS light red on ONU',
        ], $this->user->id);

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'customer_id' => $this->customer->id,
            'priority' => 'HIGH',
            'status' => 'PENDING',
        ]);

        $this->assertStringStartsWith('WO-' . date('Y') . '-', $wo->work_order_number);
    }

    public function test_ticket_conversion_to_work_order()
    {
        $ticket = Ticket::create([
            'ticket_number' => 'TCK-2025-000001',
            'customer_id' => $this->customer->id,
            'subject' => 'Intermittent Signal Loss',
            'description' => 'Frequent drops during daytime',
            'category' => 'Slow Internet',
            'priority' => 'HIGH',
            'status' => 'OPEN',
        ]);

        $woService = app(WorkOrderService::class);
        $wo = $woService->createFromTicket($ticket, [], $this->user->id);

        $this->assertEquals($ticket->id, $wo->ticket_id);
        $this->assertEquals($this->customer->id, $wo->customer_id);
        $this->assertEquals('Maintenance: Intermittent Signal Loss', $wo->title);
    }

    public function test_dispatch_assignment_and_gps_tracking()
    {
        $woService = app(WorkOrderService::class);
        $wo = $woService->createWorkOrder([
            'customer_id' => $this->customer->id,
            'title' => 'Drop Cable Splice',
        ], $this->user->id);

        $dispatchService = app(DispatchService::class);
        $dispatchService->assignTechnician($wo, $this->technician->id, $this->user->id, 'Bravo Team');

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'assigned_technician_id' => $this->technician->id,
            'status' => 'ASSIGNED',
        ]);

        $gpsService = app(WorkOrderGpsService::class);
        $gpsService->recordGpsEvent($wo, $this->technician->id, 'TRAVEL_STARTED', 14.6507, 121.0333);
        $gpsService->recordGpsEvent($wo, $this->technician->id, 'ARRIVED', 14.6507, 121.0333);

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'status' => 'ON_SITE',
        ]);
    }

    public function test_diagnostics_materials_equipment_replacement_and_completion()
    {
        $woService = app(WorkOrderService::class);
        $wo = $woService->createWorkOrder([
            'customer_id' => $this->customer->id,
            'title' => 'ONU Replacement Test',
        ], $this->user->id);

        $itemCatId = DB::table('item_categories')->insertGetId([
            'code' => 'TEST-CAT',
            'name' => 'Test Cat',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $item = Item::create([
            'sku' => 'MAT-RJ45-01',
            'name' => 'RJ45 Connectors',
            'category_id' => $itemCatId,
            'unit_of_measure' => 'PIECE',
        ]);

        $assetCatId = DB::table('asset_categories')->insertGetId([
            'code' => 'TEST-ACAT',
            'name' => 'Test Asset Cat',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $asset = Asset::create([
            'asset_tag' => 'AST-ONU-5555',
            'model_name' => 'Fiber ONU Box',
            'serial_number' => 'SN-5555-ONU',
            'mac_address' => '11:22:33:44:55:66',
            'asset_category_id' => $assetCatId,
            'current_status' => 'AVAILABLE',
        ]);

        // Record Diagnostics
        $diagService = app(WorkOrderDiagnosticService::class);
        $diagService->recordDiagnostic($wo, [
            'rx_power_dbm' => -20.50,
            'download_speed_mbps' => 98.40,
            'upload_speed_mbps' => 98.40,
            'latency_ms' => 8.0,
        ], $this->technician->id);

        $this->assertDatabaseHas('work_order_diagnostics', [
            'work_order_id' => $wo->id,
            'rx_power_dbm' => -20.50,
        ]);

        // Consume Material
        $matService = app(WorkOrderMaterialService::class);
        $matService->consumeMaterial($wo, $item->id, 4.0, null, null, $this->technician->id);

        $this->assertDatabaseHas('work_order_materials', [
            'work_order_id' => $wo->id,
            'item_id' => $item->id,
            'consumed_quantity' => 4.0,
        ]);

        // Equipment Swap
        $repService = app(EquipmentReplacementService::class);
        $repService->replaceEquipment($wo, null, 'SN-OLD-FAULTY', '00:11:22:33:44:55', $asset->id, $asset->serial_number, $asset->mac_address, 'Blown power IC', $this->technician->id);

        $this->assertDatabaseHas('work_order_equipment_replacements', [
            'work_order_id' => $wo->id,
            'new_serial_number' => 'SN-5555-ONU',
        ]);

        // Complete Job
        $compService = app(WorkOrderCompletionService::class);
        $compService->completeWorkOrder($wo, [
            'actual_root_cause' => 'Surge damaged ONU power IC',
            'corrective_action' => 'Replaced ONU box',
            'confirmed_by_name' => 'Jane Smith',
            'rating' => 5,
        ], $this->technician->id);

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'status' => 'CLOSED',
        ]);

        $this->assertDatabaseHas('work_order_customer_confirmations', [
            'work_order_id' => $wo->id,
            'confirmed_by_name' => 'Jane Smith',
            'rating' => 5,
        ]);
    }

    public function test_preventive_maintenance_schedule_generation()
    {
        $plan = MaintenancePlan::create([
            'plan_code' => 'PLAN-TEST-DAILY',
            'name' => 'Daily Node Ping Test',
            'maintenance_type' => 'PREVENTIVE',
            'frequency' => 'DAILY',
            'estimated_duration_minutes' => 30,
        ]);

        $schedule = MaintenancePlanSchedule::create([
            'maintenance_plan_id' => $plan->id,
            'customer_id' => $this->customer->id,
            'next_due_at' => now()->subHour(),
            'status' => 'ACTIVE',
            'auto_generate_wo' => true,
        ]);

        $prevService = app(PreventiveMaintenanceService::class);
        $count = $prevService->generateScheduledWorkOrders();

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('work_orders', [
            'maintenance_plan_schedule_id' => $schedule->id,
            'work_order_type' => 'PREVENTIVE',
        ]);
    }

    public function test_work_order_executive_reports()
    {
        $reportService = app(WorkOrderReportService::class);
        $metrics = $reportService->getExecutiveMetrics();

        $this->assertArrayHasKey('total_work_orders', $metrics);
        $this->assertArrayHasKey('first_time_fix_rate', $metrics);
        $this->assertArrayHasKey('sla_compliance_rate', $metrics);
    }
}
