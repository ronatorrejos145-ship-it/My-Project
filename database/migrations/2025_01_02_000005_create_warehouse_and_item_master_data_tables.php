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
        // Warehouses
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('manager_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('address')->nullable();
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Warehouse Locations (Bins / Racks / Shelves)
        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('code', 50);
            $table->string('name');
            $table->string('aisle', 50)->nullable();
            $table->string('rack', 50)->nullable();
            $table->string('shelf', 50)->nullable();
            $table->string('bin', 50)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['warehouse_id', 'code']);
        });

        // Item Categories
        Schema::create('item_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code', 50)->unique();
            $table->string('legal_name');
            $table->string('trade_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_identifier', 100)->nullable();
            $table->string('payment_terms', 100)->nullable();
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Items (Master Catalog)
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('item_code', 50)->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained('item_categories')->onDelete('restrict');
            $table->string('unit', 30)->default('PCS'); // PCS, METERS, BOX, SET, ROLL, etc.
            $table->text('description')->nullable();
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->integer('minimum_stock')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->foreignId('default_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot: Item - Supplier Relationship
        Schema::create('item_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('supplier_item_code')->nullable();
            $table->decimal('supplier_price', 15, 2)->nullable();
            $table->integer('lead_time_days')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_supplier');
        Schema::dropIfExists('items');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('item_categories');
        Schema::dropIfExists('warehouse_locations');
        Schema::dropIfExists('warehouses');
    }
};
