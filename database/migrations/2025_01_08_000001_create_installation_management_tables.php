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
        Schema::create('installation_work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number')->unique();
            $table->foreignId('application_id')->nullable()->constrained('service_applications')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('technical_survey_id')->nullable()->constrained('technical_surveys')->nullOnDelete();
            $table->foreignId('package_id')->constrained('service_packages');
            $table->foreignId('package_version_id')->constrained('service_package_versions');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('service_area_id')->nullable()->constrained('service_areas')->nullOnDelete();
            $table->foreignId('installation_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->foreignId('installation_location_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('gps_accuracy', 8, 2)->nullable();

            $table->string('work_type')->default('NEW_INSTALLATION');
            $table->string('priority')->default('NORMAL');
            $table->string('source')->default('SYSTEM');
            $table->text('reason')->nullable();

            $table->date('requested_date')->nullable();
            $table->date('target_date')->nullable();
            $table->dateTime('scheduled_start')->nullable();
            $table->dateTime('scheduled_end')->nullable();

            $table->string('assigned_team')->nullable();
            $table->foreignId('assigned_technician_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('employees')->nullOnDelete();

            $table->string('status')->default('PENDING');
            $table->string('customer_appointment_status')->nullable();
            $table->timestamp('customer_confirmation_at')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('testing_started_at')->nullable();
            $table->timestamp('acceptance_requested_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->text('failure_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('reschedule_reason')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'branch_id']);
            $table->index(['assigned_technician_id', 'status']);
            $table->index('work_order_number');
        });

        Schema::create('installation_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('installation_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->foreignId('previous_technician_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('new_technician_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('previous_team')->nullable();
            $table->string('new_team')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('assignment_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('installation_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('technician_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('team')->nullable();
            $table->string('appointment_status')->default('PROPOSED');
            $table->boolean('is_override')->default(false);
            $table->text('override_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('installation_checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('work_type')->nullable();
            $table->foreignId('package_id')->nullable()->constrained('service_packages')->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('installation_checklist_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('installation_checklist_templates')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('installation_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('installation_checklist_sections')->cascadeOnDelete();
            $table->string('item_code');
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('response_type')->default('YES_NO'); // YES_NO, PASS_FAIL, TEXT, NUMBER, SELECT, PHOTO, SIGNATURE, DATE_TIME
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('expected_value')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('installation_checklist_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('installation_checklist_items')->cascadeOnDelete();
            $table->text('response_value')->nullable();
            $table->boolean('response_bool')->nullable();
            $table->string('response_photo_path')->nullable();
            $table->boolean('is_passed')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('installation_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->string('category'); // BEFORE, SITE, MOUNTING, EQUIPMENT, SERIAL_LABEL, MAC_LABEL, CABLE_ROUTING, POWER, DURING, AFTER, FINAL, OTHER
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('checksum')->nullable();
            $table->timestamps();
        });

        Schema::create('installation_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('item_name');
            $table->string('unit')->default('pcs');
            $table->decimal('planned_qty', 10, 2)->default(0);
            $table->decimal('reserved_qty', 10, 2)->default(0);
            $table->decimal('issued_qty', 10, 2)->default(0);
            $table->decimal('consumed_qty', 10, 2)->default(0);
            $table->decimal('returned_qty', 10, 2)->default(0);
            $table->decimal('damaged_qty', 10, 2)->default(0);
            $table->decimal('variance_qty', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('installation_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->string('equipment_type');
            $table->string('model_name')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('condition_before')->default('NEW');
            $table->string('condition_after')->default('INSTALLED');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('installation_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->foreignId('tool_id')->nullable()->constrained('tools')->nullOnDelete();
            $table->string('tool_name');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('condition_on_return')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('installation_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->string('test_type'); // POWER, LINK, INTERNET, DNS, DOWNLOAD, UPLOAD, LATENCY, PACKET_LOSS, WIFI, SIGNAL, CABLE, OTHER
            $table->string('measured_value');
            $table->string('unit')->nullable();
            $table->string('result')->default('NOT_MEASURED'); // PASS, FAIL, WARNING, NOT_MEASURED
            $table->string('threshold_applied')->nullable();
            $table->string('test_source')->nullable();
            $table->string('device_used')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('performed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('installation_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->string('failure_category');
            $table->text('detailed_reason');
            $table->foreignId('failed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('failed_at')->nullable();
            $table->text('recommended_action')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('installation_reschedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->dateTime('previous_scheduled_start')->nullable();
            $table->dateTime('previous_scheduled_end')->nullable();
            $table->dateTime('new_scheduled_start')->nullable();
            $table->dateTime('new_scheduled_end')->nullable();
            $table->text('reason');
            $table->foreignId('rescheduled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('installation_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('signer_name');
            $table->string('signer_relationship')->default('OWNER');
            $table->string('acceptance_status')->default('ACCEPTED'); // ACCEPTED, ACCEPTED_WITH_ISSUES, REJECTED
            $table->text('rejection_reason')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('installation_supervisor_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('employees')->cascadeOnDelete();
            $table->string('decision'); // APPROVE, RETURN_FOR_REWORK, FAIL
            $table->text('comments')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('installation_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category')->default('TECHNICIAN'); // TECHNICIAN, SUPERVISOR, CUSTOMER, TECHNICAL, REWORK
            $table->text('note');
            $table->timestamps();
        });

        Schema::create('installation_handoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_id')->constrained('installation_work_orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('service_applications')->nullOnDelete();
            $table->foreignId('technical_survey_id')->nullable()->constrained('technical_surveys')->nullOnDelete();
            $table->foreignId('package_id')->constrained('service_packages');
            $table->foreignId('package_version_id')->constrained('service_package_versions');
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status')->default('READY_FOR_ACTIVATION'); // READY_FOR_ACTIVATION, HANDED_OFF, ACTIVATION_IN_PROGRESS, ACTIVATED, RETURNED
            $table->json('handover_data')->nullable();
            $table->timestamp('handoff_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installation_handoffs');
        Schema::dropIfExists('installation_notes');
        Schema::dropIfExists('installation_supervisor_reviews');
        Schema::dropIfExists('installation_acceptances');
        Schema::dropIfExists('installation_reschedules');
        Schema::dropIfExists('installation_failures');
        Schema::dropIfExists('installation_tests');
        Schema::dropIfExists('installation_tools');
        Schema::dropIfExists('installation_equipment');
        Schema::dropIfExists('installation_materials');
        Schema::dropIfExists('installation_photos');
        Schema::dropIfExists('installation_checklist_responses');
        Schema::dropIfExists('installation_checklist_items');
        Schema::dropIfExists('installation_checklist_sections');
        Schema::dropIfExists('installation_checklist_templates');
        Schema::dropIfExists('installation_schedules');
        Schema::dropIfExists('installation_assignments');
        Schema::dropIfExists('installation_status_histories');
        Schema::dropIfExists('installation_work_orders');
    }
};
