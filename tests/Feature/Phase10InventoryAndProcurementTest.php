<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\Tool;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GoodsReceivingService;
use App\Services\InventoryService;
use App\Services\PurchaseOrderService;
use App\Services\PurchaseRequestService;
use App\Services\ReorderService;
use App\Services\StockReservationService;
use App\Services\StocktakeService;
use App\Services\StockTransferService;
use App\Services\ToolCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class Phase10InventoryAndProcurementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Warehouse $warehouse1;
    protected Warehouse $warehouse2;
    protected Item $item;
    protected Supplier $supplier;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders::class);

        $this->adminUser = User::where('email', 'admin@isp.test')->first() ?? User::factory()->create();
        $this->warehouse1 = Warehouse::first();
        $this->warehouse2 = Warehouse::skip(1)->first() ?? Warehouse::factory()->create();
        $this->item = Item::first();
        $this->supplier = Supplier::first();
        $this->employee = Employee::first();
    }

    public function test_inventory_movement_ledger_records_stock_changes(): void
    {
        $inventoryService = app(InventoryService::class);

        $tx = $inventoryService->recordMovement(
            $this->item,
            $this->warehouse1,
            'RECEIPT',
            100.00,
            null,
            null,
            null,
            'Initial shipment',
            null,
            $this->adminUser->id
        );

        $this->assertDatabaseHas('stock_balances', [
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse1->id,
            'quantity_on_hand' => 100.00,
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'id' => $tx->id,
            'transaction_type' => 'RECEIPT',
            'quantity' => 100.00,
            'resulting_quantity' => 100.00,
        ]);
    }

    public function test_stock_reservation_prevents_over_reserving_available_quantity(): void
    {
        $inventoryService = app(InventoryService::class);
        $inventoryService->recordMovement($this->item, $this->warehouse1, 'RECEIPT', 50.00);

        $reservationService = app(StockReservationService::class);
        $res = $reservationService->reserveStock($this->item, $this->warehouse1, 30.00, null, null, 'Work order', $this->adminUser->id);

        $this->assertDatabaseHas('stock_reservations', [
            'id' => $res->id,
            'quantity_reserved' => 30.00,
            'status' => 'RESERVED',
        ]);

        // Over-reserving should fail
        $this->expectException(InvalidArgumentException::class);
        $reservationService->reserveStock($this->item, $this->warehouse1, 30.00);
    }

    public function test_stock_transfer_between_warehouses(): void
    {
        $inventoryService = app(InventoryService::class);
        $inventoryService->recordMovement($this->item, $this->warehouse1, 'RECEIPT', 100.00);

        $transferService = app(StockTransferService::class);
        $transfer = $transferService->initiateTransfer(
            $this->warehouse1,
            $this->warehouse2,
            [['item_id' => $this->item->id, 'quantity' => 40.00]],
            'Branch transfer',
            $this->adminUser->id
        );

        $this->assertDatabaseHas('stock_balances', [
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse1->id,
            'quantity_on_hand' => 60.00,
        ]);

        $transferService->receiveTransfer($transfer, $this->adminUser->id);

        $this->assertDatabaseHas('stock_balances', [
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse2->id,
            'quantity_on_hand' => 40.00,
        ]);
    }

    public function test_stocktake_creation_and_reconciliation_adjustment(): void
    {
        $inventoryService = app(InventoryService::class);
        $inventoryService->recordMovement($this->item, $this->warehouse1, 'RECEIPT', 100.00);

        $stocktakeService = app(StocktakeService::class);
        $stocktake = $stocktakeService->createStocktake(
            $this->warehouse1,
            'Annual Audit 2026',
            [['item_id' => $this->item->id, 'counted_qty' => 95.00, 'reason' => 'Missing 5 pcs']],
            $this->adminUser->id
        );

        $this->assertDatabaseHas('stocktake_items', [
            'stocktake_id' => $stocktake->id,
            'system_qty' => 100.00,
            'counted_qty' => 95.00,
            'variance_qty' => -5.00,
        ]);

        $stocktakeService->approveAndReconcile($stocktake, $this->adminUser->id);

        $this->assertDatabaseHas('stock_balances', [
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse1->id,
            'quantity_on_hand' => 95.00,
        ]);
    }

    public function test_procurement_lifecycle_and_goods_receiving(): void
    {
        $prService = app(PurchaseRequestService::class);
        $pr = $prService->createPurchaseRequest([
            'warehouse_id' => $this->warehouse1->id,
            'priority' => 'HIGH',
            'justification' => 'Low fiber cable stock',
        ], [
            ['item_id' => $this->item->id, 'quantity' => 200.00, 'estimated_unit_cost' => 12.50],
        ], $this->adminUser->id);

        $prService->approvePurchaseRequest($pr, $this->adminUser->id);

        $poService = app(PurchaseOrderService::class);
        $po = $poService->createPurchaseOrder($this->supplier, $this->warehouse1, [
            ['item_id' => $this->item->id, 'ordered_qty' => 200.00, 'unit_price' => 12.00],
        ], $pr, $this->adminUser->id);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'po_number' => $po->po_number,
            'status' => 'APPROVED',
        ]);

        $receivingService = app(GoodsReceivingService::class);
        $receipt = $receivingService->receiveGoods($po, [
            ['item_id' => $this->item->id, 'received_qty' => 200.00],
        ], 'DELIV-DOC-12345', $this->adminUser->id);

        $this->assertEquals('RECEIVED', $po->fresh()->status);
        $this->assertDatabaseHas('stock_balances', [
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse1->id,
            'quantity_on_hand' => 200.00,
        ]);
    }

    public function test_tool_checkout_and_checkin_lifecycle(): void
    {
        $tool = Tool::first();
        if (!$tool) {
            $tool = Tool::create([
                'tool_code' => 'TL-CRIMP-001',
                'category_id' => 1,
                'name' => 'RJ45 Crimping Tool',
                'status' => 'AVAILABLE',
                'condition' => 'GOOD',
            ]);
        }

        $checkoutService = app(ToolCheckoutService::class);
        $checkout = $checkoutService->checkoutTool($tool, $this->employee, '2026-05-15', 'Field work order', $this->adminUser->id);

        $this->assertEquals('ISSUED', $tool->fresh()->status);
        $this->assertEquals($this->employee->id, $tool->fresh()->assigned_employee_id);

        $checkoutService->returnTool($checkout, 'GOOD', 'Returned cleanly', $this->adminUser->id);

        $this->assertEquals('AVAILABLE', $tool->fresh()->status);
        $this->assertNull($tool->fresh()->assigned_employee_id);
    }

    public function test_inventory_and_procurement_http_controllers(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.inventory.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)->get(route('admin.procurement.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)->get(route('admin.tools.index'));
        $response->assertStatus(200);
    }
}
