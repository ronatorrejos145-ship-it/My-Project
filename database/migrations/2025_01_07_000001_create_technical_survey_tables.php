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
        // Technical Surveys Master Table
        Schema::create('technical_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('survey_number', 50)->unique();
            $table->foreignId('application_id')->nullable()->constrained('service_applications')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('package_id')->constrained('service_packages')->onDelete('restrict');
            $table->foreignId('package_version_id')->nullable()->constrained('service_package_versions')->nullOnDelete();

            $table->foreignId('technician_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('employees')->nullOnDelete();

            $table->string('survey_type', 50)->default('NEW_INSTALLATION'); // NEW_INSTALLATION, UPGRADE_ASSESSMENT, RELOCATION, ADDITIONAL_SERVICE, REPAIR_ASSESSMENT, RE_SURVEY, OTHER
            $table->string('status', 50)->default('DRAFT')->index(); // DRAFT, PENDING_ASSIGNMENT, ASSIGNED, SCHEDULED, EN_ROUTE, ON_SITE, IN_PROGRESS, PENDING_TECHNICAL_REVIEW, APPROVED, REJECTED, RESURVEY_REQUIRED, CANCELLED, EXPIRED
            $table->string('priority', 30)->default('MEDIUM'); // LOW, MEDIUM, HIGH, URGENT

            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->dateTime('resurvey_requested_at')->nullable();

            // Field GPS arrival verification
            $table->decimal('arrival_latitude', 10, 7)->nullable();
            $table->decimal('arrival_longitude', 10, 7)->nullable();
            $table->decimal('arrival_gps_accuracy', 8, 2)->nullable();
            $table->string('arrival_verification_status', 50)->default('NOT_VERIFIED'); // ARRIVED_AT_SITE, NEAR_SITE, LOCATION_MISMATCH, GPS_UNAVAILABLE
            $table->decimal('arrival_distance_meters', 10, 2)->nullable();

            // Wireless Line of Sight & Signal Summary
            $table->string('line_of_sight_status', 50)->default('NOT_APPLICABLE'); // CLEAR, PARTIAL, BLOCKED, UNKNOWN, NOT_APPLICABLE
            $table->text('line_of_sight_notes')->nullable();

            // Site Complexity & Safety
            $table->string('installation_complexity', 30)->default('NORMAL'); // EASY, NORMAL, MODERATE, DIFFICULT, VERY_DIFFICULT
            $table->string('safety_assessment', 30)->default('SAFE'); // SAFE, CAUTION, UNSAFE

            // Recommendations
            $table->string('technical_recommendation', 50)->default('RECOMMENDED'); // RECOMMENDED, RECOMMENDED_WITH_CONDITIONS, NOT_RECOMMENDED, REQUIRES_ADDITIONAL_ASSESSMENT
            $table->string('final_decision', 50)->default('PENDING'); // TECHNICALLY_FEASIBLE, FEASIBLE_WITH_CONDITIONS, NOT_FEASIBLE, REQUIRES_RESURVEY, INSUFFICIENT_DATA, PENDING
            $table->text('technical_summary')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Survey Status Histories (Immutable Log)
        Schema::create('technical_survey_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('technical_surveys')->onDelete('cascade');
            $table->string('previous_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();
        });

        // Survey Assignment Logs
        Schema::create('technical_survey_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('technical_surveys')->onDelete('cascade');
            $table->foreignId('previous_technician_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('new_technician_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
        });

        // Checklist Templates & Items
        Schema::create('technical_survey_checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('technology', 50)->default('FIBER'); // FIBER, WIRELESS, FTTH, HYBRID
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('technical_survey_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('technical_survey_checklist_templates')->onDelete('cascade');
            $table->string('section', 100)->default('SITE_INSPECTION'); // SITE_INSPECTION, PHYSICAL_SITE, TECHNICAL_LINK, SAFETY
            $table->string('item_text');
            $table->string('item_type', 30)->default('BOOLEAN'); // BOOLEAN, TEXT, NUMBER, SELECT, PHOTO, MEASUREMENT
            $table->boolean('is_required')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Survey Responses to Checklist Items
        Schema::create('technical_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('technical_surveys')->onDelete('cascade');
            $table->foreignId('checklist_item_id')->constrained('technical_survey_checklist_items')->onDelete('cascade');
            $table->text('response_value')->nullable();
            $table->boolean('pass_flag')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Field Signal & Technical Measurements
        Schema::create('technical_survey_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('technical_surveys')->onDelete('cascade');
            $table->string('measurement_type', 50); // RSSI, SNR, RSRP, NOISE_FLOOR, OPTICAL_POWER, LATENCY_MS, DISTANCE_METERS
            $table->decimal('value', 10, 2);
            $table->string('unit', 20)->default('dBm'); // dBm, dB, ms, meters
            $table->string('acceptance_status', 30)->default('PASS'); // PASS, FAIL, MARGINAL, NOT_MEASURED
            $table->string('measurement_tool')->nullable(); // e.g. OTDR Meter, Signal Fire AI-9, Optical Power Meter
            $table->timestamp('measured_at')->useCurrent();
            $table->timestamps();
        });

        // Secure Field Site Photos
        Schema::create('technical_survey_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('technical_surveys')->onDelete('cascade');
            $table->string('category', 50)->default('FACADE'); // FACADE, MOUNTING_LOCATION, ROOF, CABLE_ROUTE, LINE_OF_SIGHT, HAZARD, OTHER
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('caption')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Material Estimations
        Schema::create('technical_survey_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('technical_surveys')->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('item_name');
            $table->decimal('estimated_quantity', 10, 2)->default(1.00);
            $table->string('unit', 30)->default('PCS'); // PCS, METERS, BOX, SET
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Equipment Recommendations
        Schema::create('technical_survey_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('technical_surveys')->onDelete('cascade');
            $table->foreignId('asset_model_id')->constrained('asset_models')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->boolean('is_required')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Signatures & Acknowledgments
        Schema::create('technical_survey_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('technical_surveys')->onDelete('cascade');
            $table->string('signer_type', 50); // TECHNICIAN, CUSTOMER, SUPERVISOR
            $table->string('signer_name');
            $table->longText('signature_data')->nullable(); // Base64 signature image or token
            $table->timestamp('signed_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_survey_signatures');
        Schema::dropIfExists('technical_survey_equipment');
        Schema::dropIfExists('technical_survey_materials');
        Schema::dropIfExists('technical_survey_photos');
        Schema::dropIfExists('technical_survey_measurements');
        Schema::dropIfExists('technical_survey_responses');
        Schema::dropIfExists('technical_survey_checklist_items');
        Schema::dropIfExists('technical_survey_checklist_templates');
        Schema::dropIfExists('technical_survey_assignments');
        Schema::dropIfExists('technical_survey_status_histories');
        Schema::dropIfExists('technical_surveys');
    }
};
