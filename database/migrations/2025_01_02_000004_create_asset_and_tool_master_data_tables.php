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
        // Asset Categories
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Asset Models
        Schema::create('asset_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('asset_categories')->onDelete('restrict');
            $table->string('manufacturer');
            $table->string('model_name');
            $table->string('model_number')->nullable();
            $table->text('description')->nullable();
            $table->json('specifications')->nullable();
            $table->integer('warranty_period_months')->default(0);
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Assets
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag', 50)->unique();
            $table->foreignId('asset_category_id')->constrained('asset_categories')->onDelete('restrict');
            $table->foreignId('asset_model_id')->nullable()->constrained('asset_models')->nullOnDelete();
            $table->string('serial_number', 100)->nullable()->index();
            $table->string('mac_address', 50)->nullable()->index();
            $table->string('manufacturer')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 15, 2)->default(0.00);
            $table->date('warranty_start')->nullable();
            $table->date('warranty_end')->nullable();
            $table->string('current_status', 30)->default('AVAILABLE')->index(); // AVAILABLE, RESERVED, ASSIGNED, INSTALLED, IN_REPAIR, DAMAGED, LOST, RETIRED, DISPOSED
            $table->string('current_location')->nullable();
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('assigned_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('network_device_id')->nullable()->constrained('network_devices')->nullOnDelete();
            $table->string('condition', 50)->default('NEW'); // NEW, GOOD, FAIR, POOR, DAMAGED
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Asset Histories
        Schema::create('asset_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('previous_location')->nullable();
            $table->string('new_location')->nullable();
            $table->string('previous_status', 30)->nullable();
            $table->string('new_status', 30)->nullable();
            $table->foreignId('previous_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('new_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('previous_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('new_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('action'); // ASSIGNED, TRANSFERRED, REPAIRED, RETIRED, STATUS_CHANGE
            $table->text('reason')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });

        // Tool Categories
        Schema::create('tool_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tools
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('tool_code', 50)->unique();
            $table->foreignId('category_id')->constrained('tool_categories')->onDelete('restrict');
            $table->string('name');
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('condition', 50)->default('GOOD'); // GOOD, FAIR, POOR, DAMAGED, UNDER_REPAIR
            $table->string('status', 30)->default('AVAILABLE')->index(); // AVAILABLE, ISSUED, IN_REPAIR, LOST, RETIRED
            $table->date('purchase_date')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
        Schema::dropIfExists('tool_categories');
        Schema::dropIfExists('asset_histories');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_models');
        Schema::dropIfExists('asset_categories');
    }
};
