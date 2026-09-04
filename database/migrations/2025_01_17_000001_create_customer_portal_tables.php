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
        // 1. Customer Portal Profiles
        Schema::create('customer_portal_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('preferred_language', 10)->default('en');
            $table->string('theme', 20)->default('light');
            $table->boolean('two_factor_enabled')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 50)->nullable();
            $table->timestamps();
        });

        // 2. Customer Security Events Log
        Schema::create('customer_security_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('event_type', 50); // LOGIN, LOGOUT, PASSWORD_CHANGE, SESSION_REVOKED, PROFILE_UPDATE
            $table->string('ip_address', 50)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('created_at');
        });

        // 3. Customer Service Self-Service Requests
        Schema::create('customer_service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->string('request_type', 50); // UPGRADE, DOWNGRADE, RELOCATION, RECONNECTION, TERMINATION, EQUIPMENT_REPLACEMENT, BILLING_DISPUTE
            $table->foreignId('target_package_id')->nullable()->constrained('service_packages')->nullOnDelete();
            $table->json('payload')->nullable(); // new address, relocation coordinates, notes, etc.
            $table->string('status', 30)->default('SUBMITTED')->index(); // SUBMITTED, UNDER_REVIEW, APPROVED, IN_PROGRESS, COMPLETED, REJECTED, CANCELLED
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // 4. Customer Notification Preferences
        Schema::create('customer_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->boolean('email_billing')->default(true);
            $table->boolean('sms_billing')->default(true);
            $table->boolean('email_promotions')->default(false);
            $table->boolean('sms_promotions')->default(false);
            $table->boolean('email_outages')->default(true);
            $table->boolean('sms_outages')->default(true);
            $table->timestamps();
        });

        // 5. Customer Document Access Logs
        Schema::create('customer_document_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('document_type', 50); // INVOICE, RECEIPT, STATEMENT, CONTRACT
            $table->unsignedBigInteger('document_id');
            $table->string('ip_address', 50)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('accessed_at');
        });

        // 6. Customer Disputes
        Schema::create('customer_disputes', function (Blueprint $table) {
            $table->id();
            $table->string('dispute_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('dispute_type', 50); // BILLING_AMOUNT, UNREFLECTED_PAYMENT, SERVICE_DOWNTIME
            $table->decimal('disputed_amount', 15, 2)->default(0.00);
            $table->text('description');
            $table->string('status', 30)->default('OPEN')->index(); // OPEN, UNDER_INVESTIGATION, RESOLVED, REJECTED
            $table->text('resolution_summary')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_disputes');
        Schema::dropIfExists('customer_document_access_logs');
        Schema::dropIfExists('customer_notification_preferences');
        Schema::dropIfExists('customer_service_requests');
        Schema::dropIfExists('customer_security_events');
        Schema::dropIfExists('customer_portal_profiles');
    }
};
