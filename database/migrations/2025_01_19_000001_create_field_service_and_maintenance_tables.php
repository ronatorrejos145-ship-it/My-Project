<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source')->default('CUSTOMER_PORTAL'); // CUSTOMER_PORTAL, TICKET, TECHNICIAN, SUPERVISOR, ASSET_SCHEDULE
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('NORMAL'); // LOW, NORMAL, HIGH, URGENT, CRITICAL
            $table->string('status')->default('NEW'); // NEW, UNDER_REVIEW, APPROVED, REJECTED, CONVERTED_TO_WORK_ORDER, CANCELLED
            $table->date('preferred_date')->nullable();
            $table->string('preferred_time_slot')->nullable();
            $table->text('approval_notes')->nullable();
            $table->unsignedBigInteger('work_order_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('work_order_checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('version')->default('1.0');
            $table->string('work_order_type')->default('CORRECTIVE');
            $table->text('description')->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->timestamps();
        });

        Schema::create('work_order_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('work_order_checklist_templates')->cascadeOnDelete();
            $table->integer('step_number')->default(1);
            $table->string('item_label');
            $table->string('item_type')->default('CHECKBOX'); // CHECKBOX, YES_NO, TEXT, NUMBER, MEASUREMENT, PHOTO_REQUIRED, SIGNATURE_REQUIRED, PASS_FAIL
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        Schema::create('maintenance_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('maintenance_type')->default('PREVENTIVE'); // PREVENTIVE, INSPECTION, CALIBRATION
            $table->string('frequency')->default('MONTHLY'); // DAILY, WEEKLY, MONTHLY, QUARTERLY, SEMI_ANNUAL, ANNUAL, CUSTOM_DAYS
            $table->integer('custom_interval_days')->nullable();
            $table->string('target_asset_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('required_skills')->nullable();
            $table->integer('estimated_duration_minutes')->default(60);
            $table->foreignId('checklist_template_id')->nullable()->constrained('work_order_checklist_templates')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('maintenance_plan_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_plan_id')->constrained('maintenance_plans')->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_due_at');
            $table->integer('grace_days')->default(3);
            $table->string('status')->default('ACTIVE'); // ACTIVE, PAUSED, COMPLETED, OVERDUE
            $table->boolean('auto_generate_wo')->default(true);
            $table->timestamps();
        });

        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number')->unique(); // WO-YYYY-000001
            $table->foreignId('maintenance_request_id')->nullable()->constrained('maintenance_requests')->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('complaint_id')->nullable()->constrained('customer_complaints')->nullOnDelete();
            $table->foreignId('incident_id')->nullable()->constrained('support_incidents')->nullOnDelete();
            $table->foreignId('maintenance_plan_schedule_id')->nullable()->constrained('maintenance_plan_schedules')->nullOnDelete();
            $table->unsignedBigInteger('parent_work_order_id')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->string('work_order_type')->default('CORRECTIVE'); // CORRECTIVE, PREVENTIVE, EMERGENCY, INSTALLATION_RELATED, EQUIPMENT_REPLACEMENT, INSPECTION, REPAIR, RELOCATION_RELATED, CUSTOMER_REQUEST, INCIDENT_RESPONSE, FOLLOW_UP, OTHER
            $table->string('status')->default('PENDING'); // DRAFT, PENDING, APPROVED, ASSIGNED, SCHEDULED, EN_ROUTE, ON_SITE, IN_PROGRESS, WAITING_MATERIALS, WAITING_CUSTOMER, WAITING_EXTERNAL, TESTING, COMPLETED, FAILED, RESCHEDULED, CANCELLED, CLOSED
            $table->string('priority')->default('NORMAL'); // LOW, NORMAL, HIGH, URGENT, CRITICAL
            $table->string('severity')->default('MODERATE'); // MINOR, MODERATE, MAJOR, CRITICAL
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('symptoms')->nullable();
            $table->text('suspected_cause')->nullable();
            $table->text('actual_root_cause')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('preventive_action')->nullable();
            $table->string('service_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('location_accuracy', 8, 2)->nullable();
            $table->timestamp('scheduled_start_at')->nullable();
            $table->timestamp('scheduled_end_at')->nullable();
            $table->timestamp('actual_start_at')->nullable();
            $table->timestamp('actual_end_at')->nullable();
            $table->timestamp('arrival_at')->nullable();
            $table->timestamp('completion_at')->nullable();
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('territory_code')->nullable();
            $table->boolean('is_billable')->default(false);
            $table->decimal('estimated_cost', 15, 2)->default(0.00);
            $table->decimal('actual_cost', 15, 2)->default(0.00);
            $table->timestamp('response_due_at')->nullable();
            $table->timestamp('restoration_due_at')->nullable();
            $table->timestamp('resolution_due_at')->nullable();
            $table->boolean('is_sla_breached')->default(false);
            $table->timestamp('sla_breached_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_work_order_id')->references('id')->on('work_orders')->nullOnDelete();
        });

        // Add foreign key constraint back to maintenance_requests for work_order_id
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->foreign('work_order_id')->references('id')->on('work_orders')->nullOnDelete();
        });

        Schema::create('work_order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('team_name')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->string('status')->default('ASSIGNED'); // ASSIGNED, ACCEPTED, REJECTED, REASSIGNED, COMPLETED
            $table->text('notes')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_gps_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_type'); // TRAVEL_STARTED, ARRIVED, WORK_STARTED, WORK_PAUSED, WORK_RESUMED, TESTING_STARTED, COMPLETED, DEPARTED
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('location_accuracy', 8, 2)->nullable();
            $table->string('device_info')->nullable();
            $table->boolean('is_flagged_suspicious')->default(false);
            $table->timestamps();
        });

        Schema::create('work_order_checklist_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('work_order_checklist_templates')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('work_order_checklist_items')->cascadeOnDelete();
            $table->text('result_value')->nullable();
            $table->boolean('is_passed')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_diagnostics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('device_powered')->default(true);
            $table->string('wan_status')->nullable();
            $table->string('lan_status')->nullable();
            $table->string('wifi_status')->nullable();
            $table->string('cable_condition')->nullable();
            $table->string('connector_condition')->nullable();
            $table->decimal('rx_power_dbm', 8, 2)->nullable();
            $table->decimal('tx_power_dbm', 8, 2)->nullable();
            $table->decimal('download_speed_mbps', 8, 2)->nullable();
            $table->decimal('upload_speed_mbps', 8, 2)->nullable();
            $table->decimal('latency_ms', 8, 2)->nullable();
            $table->decimal('packet_loss_percent', 5, 2)->nullable();
            $table->text('diagnosis_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('photo_category')->default('AFTER'); // BEFORE, DURING, AFTER, EQUIPMENT, DAMAGE, CABLE, INSTALLATION, SITE, TEST_RESULT, CUSTOMER_PROOF, OTHER
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('serial_number')->nullable();
            $table->decimal('required_quantity', 12, 2)->default(1.00);
            $table->decimal('issued_quantity', 12, 2)->default(0.00);
            $table->decimal('consumed_quantity', 12, 2)->default(0.00);
            $table->decimal('returned_quantity', 12, 2)->default(0.00);
            $table->decimal('damaged_quantity', 12, 2)->default(0.00);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('total_cost', 15, 2)->default(0.00);
            $table->string('status')->default('REQUIRED'); // REQUIRED, RESERVED, ISSUED, CONSUMED, RETURNED
            $table->timestamps();
        });

        Schema::create('work_order_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('tool_id')->constrained('tools')->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->string('condition_before')->nullable();
            $table->string('condition_after')->nullable();
            $table->boolean('is_damaged')->default(false);
            $table->text('damage_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_equipment_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->foreignId('old_asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->string('old_serial_number')->nullable();
            $table->string('old_mac_address')->nullable();
            $table->foreignId('new_asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->string('new_serial_number')->nullable();
            $table->string('new_mac_address')->nullable();
            $table->text('replacement_reason')->nullable();
            $table->string('disposed_or_returned_status')->default('RETURNED_TO_WAREHOUSE');
            $table->foreignId('replaced_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replaced_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('work_order_time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->string('entry_type')->default('ACTIVE_WORK'); // TRAVEL, ON_SITE, ACTIVE_WORK, TESTING, PAUSED
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_customer_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('confirmed_by_name')->nullable();
            $table->string('signature_file_path')->nullable();
            $table->integer('rating')->nullable();
            $table->text('customer_comments')->nullable();
            $table->timestamp('confirmed_at')->useCurrent();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->string('failure_reason'); // CUSTOMER_UNAVAILABLE, NO_ACCESS, WRONG_ADDRESS, UNSAFE_SITE, EQUIPMENT_UNAVAILABLE, MATERIAL_UNAVAILABLE, TECHNICIAN_ISSUE, WEATHER, EXTERNAL_DEPENDENCY, CUSTOMER_CANCELLATION, OTHER
            $table->text('notes')->nullable();
            $table->foreignId('reported_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('requires_revisit')->default(true);
            $table->date('rescheduled_date')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_revisits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('follow_up_work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_downtime', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->timestamp('outage_start_at');
            $table->timestamp('outage_end_at')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->boolean('is_service_restored')->default(true);
            $table->foreignId('restoration_verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('technician_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->json('working_days')->nullable(); // [1,2,3,4,5]
            $table->time('work_start_time')->default('08:00:00');
            $table->time('work_end_time')->default('17:00:00');
            $table->boolean('is_on_leave')->default(false);
            $table->string('current_status')->default('AVAILABLE'); // AVAILABLE, BUSY, EN_ROUTE, ON_SITE, OFF_DUTY
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->timestamp('last_gps_at')->nullable();
            $table->timestamps();
        });

        Schema::create('technician_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->string('skill_name');
            $table->string('proficiency_level')->default('INTERMEDIATE'); // BEGINNER, INTERMEDIATE, ADVANCED, EXPERT
            $table->boolean('is_certified')->default(false);
            $table->timestamps();
        });

        Schema::create('technician_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->string('certification_name');
            $table->string('certification_number')->nullable();
            $table->string('issuing_authority')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('verification_status')->default('VERIFIED'); // PENDING, VERIFIED, EXPIRED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_certifications');
        Schema::dropIfExists('technician_skills');
        Schema::dropIfExists('technician_availability');
        Schema::dropIfExists('work_order_downtime');
        Schema::dropIfExists('work_order_revisits');
        Schema::dropIfExists('work_order_failures');
        Schema::dropIfExists('work_order_customer_confirmations');
        Schema::dropIfExists('work_order_time_entries');
        Schema::dropIfExists('work_order_equipment_replacements');
        Schema::dropIfExists('work_order_tools');
        Schema::dropIfExists('work_order_materials');
        Schema::dropIfExists('work_order_photos');
        Schema::dropIfExists('work_order_diagnostics');
        Schema::dropIfExists('work_order_checklist_results');
        Schema::dropIfExists('work_order_gps_events');
        Schema::dropIfExists('work_order_assignments');
        Schema::dropIfExists('work_order_status_histories');

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['work_order_id']);
        });

        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('maintenance_plan_schedules');
        Schema::dropIfExists('maintenance_plans');
        Schema::dropIfExists('work_order_checklist_items');
        Schema::dropIfExists('work_order_checklist_templates');
        Schema::dropIfExists('maintenance_requests');
    }
};
