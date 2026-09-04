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
        Schema::create('asset_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('old_condition')->nullable();
            $table->string('new_condition')->nullable();
            $table->string('old_location')->nullable();
            $table->string('new_location')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('assigned_to_type')->nullable(); // App\Models\Customer, App\Models\Employee, App\Models\Warehouse, App\Models\NetworkNode
            $table->unsignedBigInteger('assigned_to_id')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['assigned_to_type', 'assigned_to_id']);
        });

        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('destination_type')->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, IN_TRANSIT, COMPLETED, CANCELLED
            $table->foreignId('authorized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('transferred_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('condition_on_transfer')->default('GOOD');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_audit_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_number')->unique();
            $table->string('name');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('responsible_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('DRAFT'); // DRAFT, IN_PROGRESS, COMPLETED, CANCELLED
            $table->integer('expected_count')->default(0);
            $table->integer('verified_count')->default(0);
            $table->integer('discrepancy_count')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('asset_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('audit_session_id')->nullable()->constrained('asset_audit_sessions')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('physical_presence')->default('FOUND'); // FOUND, NOT_FOUND, WRONG_LOCATION, DISCREPANCY
            $table->string('condition')->default('GOOD');
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('old_asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('new_asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('installation_id')->nullable()->constrained('installation_work_orders')->nullOnDelete();
            $table->foreignId('replaced_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replaced_at')->nullable();
            $table->text('reason');
            $table->string('old_asset_condition')->default('DAMAGED');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_retirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('retired_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('retired_at')->nullable();
            $table->text('reason');
            $table->decimal('residual_value', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('disposal_number')->unique();
            $table->string('disposal_method'); // SOLD, SCRAPPED, RECYCLED, DONATED
            $table->foreignId('disposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('authorized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disposed_at')->nullable();
            $table->decimal('sale_price', 15, 2)->default(0.00);
            $table->string('certificate_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('incident_type'); // LOST, STOLEN, DAMAGED, FOUND
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('incident_date');
            $table->string('last_known_location')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('detailed_description');
            $table->string('status')->default('REPORTED'); // REPORTED, UNDER_INVESTIGATION, RESOLVED, CLOSED
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('document_type'); // INVOICE, WARRANTY, RECEIVING, DISPOSAL, INSPECTION, REPAIR, OTHER
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('checksum')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('photo_category'); // EQUIPMENT, SERIAL_LABEL, MAC_LABEL, DAMAGE, VERIFICATION, INSTALLATION
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('checksum')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_interfaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('interface_name');
            $table->string('interface_type')->default('ETHERNET'); // ETHERNET, WIRELESS, FIBER, SFP, MANAGEMENT
            $table->string('mac_address')->nullable();
            $table->string('ip_address')->nullable();
            $table->integer('vlan')->nullable();
            $table->integer('speed_mbps')->nullable();
            $table->string('status')->default('UP');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_interfaces');
        Schema::dropIfExists('asset_photos');
        Schema::dropIfExists('asset_documents');
        Schema::dropIfExists('asset_incidents');
        Schema::dropIfExists('asset_disposals');
        Schema::dropIfExists('asset_retirements');
        Schema::dropIfExists('asset_replacements');
        Schema::dropIfExists('asset_verifications');
        Schema::dropIfExists('asset_audit_sessions');
        Schema::dropIfExists('asset_transfers');
        Schema::dropIfExists('asset_assignments');
        Schema::dropIfExists('asset_status_histories');
    }
};
