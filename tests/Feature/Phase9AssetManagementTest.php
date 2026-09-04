<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;
use App\Services\AssetAssignmentService;
use App\Services\AssetDisposalService;
use App\Services\AssetReceivingService;
use App\Services\AssetReplacementService;
use App\Services\AssetRetirementService;
use App\Services\AssetService;
use App\Services\AssetTransferService;
use App\Services\AssetVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class Phase9AssetManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $techUser;
    protected Customer $customer;
    protected Employee $employee;
    protected AssetCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders::class);

        $this->adminUser = User::where('email', 'admin@isp.test')->first() ?? User::factory()->create();
        $this->customer = Customer::first();
        $this->employee = Employee::first();
        $this->category = AssetCategory::first() ?? AssetCategory::create(['code' => 'ROUTER', 'name' => 'WiFi Routers']);
    }

    public function test_can_receive_serialized_asset_with_normalized_serial_and_mac(): void
    {
        $receivingService = app(AssetReceivingService::class);

        $asset = $receivingService->receiveAsset([
            'asset_category_id' => $this->category->id,
            'serial_number' => ' sn-test-999888 ',
            'mac_address' => 'aa-bb-cc-dd-ee-ff',
            'manufacturer' => 'MikroTik',
            'purchase_cost' => 1500.00,
            'condition' => 'NEW',
        ], $this->adminUser->id);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'serial_number' => 'SN-TEST-999888',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'current_status' => 'AVAILABLE',
        ]);
    }

    public function test_prevents_duplicate_active_serial_and_mac_addresses(): void
    {
        $receivingService = app(AssetReceivingService::class);

        $receivingService->receiveAsset([
            'asset_category_id' => $this->category->id,
            'serial_number' => 'SN-DUP-123',
            'mac_address' => 'AA:11:22:33:44:55',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $receivingService->receiveAsset([
            'asset_category_id' => $this->category->id,
            'serial_number' => 'SN-DUP-123',
        ]);
    }

    public function test_assigns_equipment_to_customer_and_employee(): void
    {
        $receivingService = app(AssetReceivingService::class);
        $asset = $receivingService->receiveAsset([
            'asset_category_id' => $this->category->id,
            'serial_number' => 'SN-ASSIGN-1',
        ]);

        $assignmentService = app(AssetAssignmentService::class);
        $assignmentService->assignToCustomer($asset, $this->customer, 'New installation', $this->adminUser->id);

        $this->assertEquals('INSTALLED', $asset->fresh()->current_status);
        $this->assertEquals($this->customer->id, $asset->fresh()->assigned_customer_id);

        // Assign another asset to employee
        $asset2 = $receivingService->receiveAsset([
            'asset_category_id' => $this->category->id,
            'serial_number' => 'SN-ASSIGN-2',
        ]);
        $assignmentService->assignToEmployee($asset2, $this->employee, 'Field tech spare', $this->adminUser->id);

        $this->assertEquals('ASSIGNED', $asset2->fresh()->current_status);
        $this->assertEquals($this->employee->id, $asset2->fresh()->assigned_employee_id);
    }

    public function test_transfers_asset_between_warehouse_and_branch(): void
    {
        $receivingService = app(AssetReceivingService::class);
        $asset = $receivingService->receiveAsset([
            'asset_category_id' => $this->category->id,
            'serial_number' => 'SN-XFER-1',
        ]);

        $transferService = app(AssetTransferService::class);
        $transfer = $transferService->initiateTransfer($asset, 'App\\Models\\Branch', 1, 'Branch dispatch', $this->adminUser->id);

        $this->assertEquals('IN_TRANSIT', $asset->fresh()->current_status);

        $transferService->completeTransfer($transfer, $this->adminUser->id);
        $this->assertEquals('AVAILABLE', $asset->fresh()->current_status);
    }

    public function test_replaces_damaged_customer_equipment(): void
    {
        $receivingService = app(AssetReceivingService::class);
        $oldAsset = $receivingService->receiveAsset([
            'asset_category_id' => $this->category->id,
            'serial_number' => 'SN-OLD-001',
        ]);

        $newAsset = $receivingService->receiveAsset([
            'asset_category_id' => $this->category->id,
            'serial_number' => 'SN-NEW-002',
        ]);

        $assignmentService = app(AssetAssignmentService::class);
        $assignmentService->assignToCustomer($oldAsset, $this->customer);

        $replacementService = app(AssetReplacementService::class);
        $replacement = $replacementService->replaceEquipment($oldAsset, $newAsset, $this->customer, null, 'Power surge damage', 'DAMAGED', null, $this->adminUser->id);

        $this->assertEquals('IN_REPAIR', $oldAsset->fresh()->current_status);
        $this->assertEquals('INSTALLED', $newAsset->fresh()->current_status);
        $this->assertEquals($this->customer->id, $newAsset->fresh()->assigned_customer_id);
    }

    public function test_verifies_physical_asset_via_qr_audit(): void
    {
        $receivingService = app(AssetReceivingService::class);
        $asset = $receivingService->receiveAsset([
            'asset_category_id' => $this->category->id,
            'serial_number' => 'SN-VERIFY-001',
        ]);

        $verificationService = app(AssetVerificationService::class);
        $verificationService->recordVerification($asset, null, 'FOUND', 'GOOD', 14.599, 120.984, 'Verified on rack', null, $this->adminUser->id);

        $this->assertDatabaseHas('asset_verifications', [
            'asset_id' => $asset->id,
            'physical_presence' => 'FOUND',
            'condition' => 'GOOD',
        ]);
    }

    public function test_retires_and_disposes_obsolete_asset(): void
    {
        $receivingService = app(AssetReceivingService::class);
        $asset = $receivingService->receiveAsset([
            'asset_category_id' => $this->category->id,
            'serial_number' => 'SN-RETIRE-001',
        ]);

        $retirementService = app(AssetRetirementService::class);
        $retirementService->retireAsset($asset, 'Obsolete hardware', 100.00, null, $this->adminUser->id);

        $this->assertEquals('RETIRED', $asset->fresh()->current_status);

        $disposalService = app(AssetDisposalService::class);
        $disposalService->disposeAsset($asset, 'SCRAPPED', 0.00, 'CERT-SCRAP-999', null, $this->adminUser->id);

        $this->assertEquals('DISPOSED', $asset->fresh()->current_status);
    }
}
