<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Stock Balances per Item & Warehouse Location
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('warehouse_locations')->nullOnDelete();
            $table->decimal('quantity_on_hand', 15, 2)->default(0.00);
            $table->decimal('quantity_reserved', 15, 2)->default(0.00);
            $table->decimal('quantity_damaged', 15, 2)->default(0.00);
            $table->decimal('quantity_in_transit', 15, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['item_id', 'warehouse_id', 'location_id'], 'stock_balance_unique');
        });

        // Immutable Inventory Movement Ledger Transactions
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('warehouse_locations')->nullOnDelete();
            $table->string('transaction_type'); // RECEIPT, ISSUE, RETURN, TRANSFER_IN, TRANSFER_OUT, RESERVATION, ADJUSTMENT_IN, ADJUSTMENT_OUT, DAMAGE, LOSS
            $table->decimal('quantity', 15, 2);
            $table->decimal('previous_quantity', 15, 2);
            $table->decimal('resulting_quantity', 15, 2);
            $table->string('unit', 30)->default('pcs');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Stock Reservations
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number')->unique();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->decimal('quantity_reserved', 15, 2);
            $table->string('reference_type')->nullable(); // InstallationWorkOrder, Maintenance, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('status')->default('RESERVED'); // RESERVED, FULFILLED, EXPIRED, RELEASED
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Material Requests
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('priority')->default('NORMAL');
            $table->string('status')->default('SUBMITTED'); // DRAFT, SUBMITTED, APPROVED, PARTIALLY_FULFILLED, FULFILLED, REJECTED, CANCELLED
            $table->date('required_date')->nullable();
            $table->string('purpose')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('material_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_request_id')->constrained('material_requests')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('requested_qty', 15, 2);
            $table->decimal('issued_qty', 15, 2)->default(0.00);
            $table->string('unit', 30)->default('pcs');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Stock Transfers
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('source_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('destination_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('status')->default('REQUESTED'); // REQUESTED, APPROVED, DISPATCHED, IN_TRANSIT, RECEIVED, CANCELLED
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('inventory_transfers')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('requested_qty', 15, 2);
            $table->decimal('dispatched_qty', 15, 2)->default(0.00);
            $table->decimal('received_qty', 15, 2)->default(0.00);
            $table->string('unit', 30)->default('pcs');
            $table->timestamps();
        });

        // Stocktakes
        Schema::create('stocktakes', function (Blueprint $table) {
            $table->id();
            $table->string('stocktake_number')->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('title');
            $table->date('stocktake_date');
            $table->string('status')->default('IN_PROGRESS'); // DRAFT, IN_PROGRESS, REVIEW, COMPLETED, CANCELLED
            $table->foreignId('conducted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stocktake_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stocktake_id')->constrained('stocktakes')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('system_qty', 15, 2);
            $table->decimal('counted_qty', 15, 2);
            $table->decimal('variance_qty', 15, 2);
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        // Purchase Requests & Procurement
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number')->unique();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('priority')->default('NORMAL');
            $table->string('status')->default('SUBMITTED'); // DRAFT, SUBMITTED, APPROVED, REJECTED, PO_CREATED, CANCELLED
            $table->date('required_date')->nullable();
            $table->decimal('estimated_total', 15, 2)->default(0.00);
            $table->text('justification')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('quantity', 15, 2);
            $table->decimal('estimated_unit_cost', 15, 2)->default(0.00);
            $table->decimal('estimated_subtotal', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // Supplier Quotations
        Schema::create('supplier_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('purchase_request_id')->nullable()->constrained('purchase_requests')->nullOnDelete();
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->string('status')->default('RECEIVED'); // RECEIVED, SELECTED, REJECTED
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Purchase Orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('purchase_request_id')->nullable()->constrained('purchase_requests')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('order_date');
            $table->date('expected_delivery')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->string('status')->default('APPROVED'); // DRAFT, PENDING_APPROVAL, APPROVED, SENT, PARTIALLY_RECEIVED, RECEIVED, CANCELLED
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('ordered_qty', 15, 2);
            $table->decimal('received_qty', 15, 2)->default(0.00);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();
        });

        // Goods Receipts
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->string('delivery_document_number')->nullable();
            $table->string('inspection_status')->default('ACCEPTED'); // ACCEPTED, ACCEPTED_WITH_ISSUES, REJECTED
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('received_qty', 15, 2);
            $table->decimal('unit_cost', 15, 2);
            $table->timestamps();
        });

        // Tool Checkouts, Inspections & Calibrations
        Schema::create('tool_checkouts', function (Blueprint $table) {
            $table->id();
            $table->string('checkout_number')->unique();
            $table->foreignId('tool_id')->constrained('tools')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expected_return_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('condition_on_issue')->default('GOOD');
            $table->string('condition_on_return')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('tool_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('tools')->cascadeOnDelete();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('inspected_at')->nullable();
            $table->string('result')->default('PASS'); // PASS, FAIL, REPAIR_REQUIRED
            $table->string('condition')->default('GOOD');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('tool_calibrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('tools')->cascadeOnDelete();
            $table->date('calibration_date');
            $table->date('next_calibration_due');
            $table->string('provider_name')->nullable();
            $table->string('certificate_number')->nullable();
            $table->string('status')->default('VALID'); // VALID, DUE, EXPIRED
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_calibrations');
        Schema::dropIfExists('tool_inspections');
        Schema::dropIfExists('tool_checkouts');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('supplier_quotations');
        Schema::dropIfExists('purchase_request_items');
        Schema::dropIfExists('purchase_requests');
        Schema::dropIfExists('stocktake_items');
        Schema::dropIfExists('stocktakes');
        Schema::dropIfExists('inventory_transfer_items');
        Schema::dropIfExists('inventory_transfers');
        Schema::dropIfExists('material_request_items');
        Schema::dropIfExists('material_requests');
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('stock_balances');
    }
};
