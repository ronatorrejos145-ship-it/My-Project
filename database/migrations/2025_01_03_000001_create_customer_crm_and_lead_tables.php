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
        // Enhance customers table with detailed CRM fields
        Schema::table('customers', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('user_id');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('suffix', 20)->nullable()->after('last_name');
            $table->string('legal_name')->nullable()->after('suffix');
            $table->string('business_name')->nullable()->after('legal_name');
            $table->string('trade_name')->nullable()->after('business_name');
            $table->date('date_of_birth')->nullable()->after('trade_name');
            $table->string('occupation')->nullable()->after('date_of_birth');

            $table->foreignId('branch_id')->nullable()->after('customer_type')->constrained('branches')->nullOnDelete();
            $table->foreignId('assigned_employee_id')->nullable()->after('branch_id')->constrained('employees')->nullOnDelete();
            $table->foreignId('referred_by_customer_id')->nullable()->after('assigned_employee_id')->constrained('customers')->nullOnDelete();
            $table->string('acquisition_source', 50)->default('WALK_IN')->after('referred_by_customer_id');

            $table->foreignId('primary_address_id')->nullable()->after('billing_address')->constrained('addresses')->nullOnDelete();
            $table->foreignId('installation_address_id')->nullable()->after('primary_address_id')->constrained('addresses')->nullOnDelete();
            $table->foreignId('billing_address_id')->nullable()->after('installation_address_id')->constrained('addresses')->nullOnDelete();

            $table->decimal('credit_limit', 15, 2)->default(0.00)->after('current_balance');
            $table->softDeletes()->after('updated_at');
        });

        // Customer Status History (Immutable log)
        Schema::create('customer_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 50)->default('SYSTEM'); // SYSTEM, MANUAL, AUTOMATED_BILLING, API
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();
        });

        // Customer Contacts (Multiple contacts per customer)
        Schema::create('customer_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('name');
            $table->string('relationship', 50)->default('PRIMARY'); // PRIMARY, BILLING, TECHNICAL, AUTHORIZED_REP, OWNER, EMERGENCY
            $table->string('position')->nullable();
            $table->string('mobile', 50);
            $table->string('telephone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('preferred_contact_method', 30)->default('MOBILE'); // MOBILE, EMAIL, SMS, TELEPHONE
            $table->string('authorization_level', 50)->default('FULL'); // FULL, BILLING_ONLY, TECHNICAL_ONLY, NONE
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();
        });

        // Customer Address Histories
        Schema::create('customer_address_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('address_id')->constrained('addresses')->onDelete('cascade');
            $table->string('address_type', 50)->default('INSTALLATION');
            $table->dateTime('effective_from');
            $table->dateTime('effective_until')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        // Customer Secure Document Metadata
        Schema::create('customer_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('document_type', 50); // VALID_ID, PROOF_OF_ADDRESS, BUSINESS_REG, AUTHORIZATION_LETTER, CONTRACT, APPLICATION, OTHER
            $table->string('document_number', 100)->nullable();
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->string('checksum', 64)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->date('expiration_date')->nullable();
            $table->string('verification_status', 30)->default('PENDING')->index(); // PENDING, VERIFIED, REJECTED, EXPIRED
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Customer Internal Notes
        Schema::create('customer_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('note_type', 50)->default('GENERAL');
            $table->text('note');
            $table->string('visibility', 50)->default('INTERNAL'); // INTERNAL, CUSTOMER_SERVICE, SALES, TECHNICAL, FINANCE, MANAGEMENT
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Customer Tags & Pivot
        Schema::create('customer_tags', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('color_code', 20)->default('#6366F1');
            $table->text('description')->nullable();
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('customer_customer_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('customer_tag_id')->constrained('customer_tags')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['customer_id', 'customer_tag_id']);
        });

        // Customer Referrals
        Schema::create('customer_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('referred_customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('referral_code', 50)->nullable();
            $table->date('referral_date');
            $table->string('status', 30)->default('PENDING'); // PENDING, QUALIFIED, REWARDED, EXPIRED
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Customer Assignment History
        Schema::create('customer_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('previous_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('new_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('previous_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('new_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->dateTime('effective_date');
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Customer Consents
        Schema::create('customer_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('consent_type', 50); // TERMS_AND_CONDITIONS, PRIVACY_POLICY, SERVICE_AGREEMENT, MARKETING
            $table->string('version', 20)->default('1.0');
            $table->string('status', 30)->default('ACCEPTED'); // ACCEPTED, REVOKED
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('source', 50)->default('PORTAL');
            $table->timestamps();
        });

        // Universal Customer Activity Timeline
        Schema::create('customer_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('activity_type', 50)->index(); // CUSTOMER_CREATED, CUSTOMER_UPDATED, STATUS_CHANGED, DOCUMENT_UPLOADED, DOCUMENT_VERIFIED, ASSIGNMENT_CHANGED, LEAD_CONVERTED, APPLICATION_CREATED, SUBSCRIPTION_CREATED, PAYMENT_RECEIVED, TICKET_CREATED, INSTALLATION_COMPLETED, MAINTENANCE_COMPLETED
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });

        // Leads Architecture
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_number', 50)->unique();
            $table->foreignId('converted_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('mobile', 50);
            $table->string('telephone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('source', 50)->default('WALK_IN'); // WALK_IN, WEBSITE, FACEBOOK, REFERRAL, FIELD_SALES, SALES_AGENT, PARTNER, ADVERTISEMENT, OTHER
            $table->string('campaign')->nullable();
            $table->foreignId('referral_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('interested_package_id')->nullable()->constrained('service_packages')->nullOnDelete();
            $table->string('priority', 30)->default('MEDIUM'); // LOW, MEDIUM, HIGH, URGENT
            $table->string('status', 30)->default('NEW')->index(); // NEW, CONTACTED, QUALIFIED, UNQUALIFIED, FOLLOW_UP, CONVERTED, LOST, CLOSED
            $table->date('expected_conversion_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Lead Status Histories
        Schema::create('lead_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();
        });

        // Lead Activities & Follow-ups
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('activity_type', 50)->default('PHONE_CALL'); // PHONE_CALL, SMS, EMAIL, VISIT, ONLINE_INQUIRY, FOLLOW_UP, MEETING, OTHER
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->string('outcome')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('next_follow_up_at')->nullable();
            $table->string('status', 30)->default('PENDING'); // PENDING, COMPLETED, CANCELLED
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('lead_status_histories');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('customer_activities');
        Schema::dropIfExists('customer_consents');
        Schema::dropIfExists('customer_assignments');
        Schema::dropIfExists('customer_referrals');
        Schema::dropIfExists('customer_customer_tag');
        Schema::dropIfExists('customer_tags');
        Schema::dropIfExists('customer_notes');
        Schema::dropIfExists('customer_documents');
        Schema::dropIfExists('customer_address_histories');
        Schema::dropIfExists('customer_contacts');
        Schema::dropIfExists('customer_status_histories');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['assigned_employee_id']);
            $table->dropForeign(['referred_by_customer_id']);
            $table->dropForeign(['primary_address_id']);
            $table->dropForeign(['installation_address_id']);
            $table->dropForeign(['billing_address_id']);

            $table->dropColumn([
                'first_name', 'middle_name', 'last_name', 'suffix', 'legal_name', 'business_name', 'trade_name', 'date_of_birth', 'occupation',
                'branch_id', 'assigned_employee_id', 'referred_by_customer_id', 'acquisition_source',
                'primary_address_id', 'installation_address_id', 'billing_address_id', 'credit_limit', 'deleted_at'
            ]);
        });
    }
};
