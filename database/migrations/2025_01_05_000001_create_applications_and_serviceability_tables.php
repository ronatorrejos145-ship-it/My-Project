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
        // Service Applications Master Record
        Schema::create('service_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('applicant_type', 50)->default('INDIVIDUAL'); // INDIVIDUAL, RESIDENTIAL, BUSINESS, CORPORATE, GOVERNMENT
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('business_name')->nullable();
            $table->string('primary_phone', 50);
            $table->string('secondary_phone', 50)->nullable();
            $table->string('email')->nullable();

            $table->foreignId('service_package_id')->constrained('service_packages')->onDelete('restrict');
            $table->foreignId('service_package_version_id')->nullable()->constrained('service_package_versions')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('service_area_id')->nullable()->constrained('service_areas')->nullOnDelete();
            $table->foreignId('installation_address_id')->nullable()->constrained('addresses')->nullOnDelete();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('gps_accuracy', 8, 2)->nullable();
            $table->string('location_source', 50)->default('MAP_PIN'); // GPS, MAP_PIN, MANUAL, GEOCODE, STAFF_ENTERED

            $table->string('status', 50)->default('SUBMITTED')->index(); // DRAFT, SUBMITTED, UNDER_REVIEW, SERVICEABILITY_CHECK, REQUIRES_SURVEY, PENDING_DOCUMENTS, APPROVED, REJECTED, CANCELLED, EXPIRED, CONVERTED
            $table->string('application_source', 50)->default('ONLINE_PORTAL'); // ONLINE_PORTAL, WALK_IN, FIELD_AGENT, PHONE_INQUIRY

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Application Status Histories (Immutable Log)
        Schema::create('service_application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('service_applications')->onDelete('cascade');
            $table->string('previous_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();
        });

        // Audit log of Technical Serviceability Engine evaluations
        Schema::create('serviceability_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained('service_applications')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('package_id')->constrained('service_packages')->onDelete('restrict');
            $table->foreignId('package_version_id')->nullable()->constrained('service_package_versions')->nullOnDelete();

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('gps_accuracy', 8, 2)->nullable();
            $table->foreignId('service_area_id')->nullable()->constrained('service_areas')->nullOnDelete();

            $table->string('result_status', 50)->index(); // SERVICEABLE, REQUIRES_TECHNICAL_SURVEY, OUT_OF_COVERAGE, CAPACITY_UNAVAILABLE, PACKAGE_UNAVAILABLE, TECHNOLOGY_UNAVAILABLE, INVALID_LOCATION, MANUAL_REVIEW
            $table->string('reason_code', 100); // e.g., COVERAGE_OK_DISTANCE_OK, NO_SERVICE_AREA, DISTANCE_EXCEEDED, CAPACITY_FULL, OUT_OF_BOUNDS
            $table->text('explanation')->nullable();

            $table->foreignId('nearest_node_id')->nullable()->constrained('network_nodes')->nullOnDelete();
            $table->foreignId('nearest_access_point_id')->nullable()->constrained('access_points')->nullOnDelete();
            $table->foreignId('nearest_nanobox_id')->nullable()->constrained('network_devices')->nullOnDelete();

            $table->decimal('calculated_distance_meters', 10, 2)->nullable();
            $table->string('capacity_status', 50)->default('CAPACITY_UNKNOWN'); // CAPACITY_AVAILABLE, CAPACITY_LIMIT_REACHED, CAPACITY_UNKNOWN

            $table->timestamp('checked_at')->useCurrent();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('engine_version', 20)->default('1.0.0');

            $table->boolean('is_overridden')->default(false);
            $table->string('override_result_status', 50)->nullable();
            $table->text('override_reason')->nullable();
            $table->foreignId('overridden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('overridden_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Application Documents Attachments
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('service_applications')->onDelete('cascade');
            $table->string('document_type', 50); // VALID_ID, PROOF_OF_ADDRESS, BUSINESS_REG, AUTHORIZATION_LETTER, OTHER
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->string('checksum', 64)->nullable();

            $table->string('verification_status', 30)->default('UNDER_REVIEW')->index(); // REQUIRED, UPLOADED, UNDER_REVIEW, VERIFIED, REJECTED, EXPIRED
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('serviceability_checks');
        Schema::dropIfExists('service_application_status_histories');
        Schema::dropIfExists('service_applications');
    }
};
