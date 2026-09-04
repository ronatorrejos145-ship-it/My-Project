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
        // Service Accounts (One Customer can have multiple Service Accounts)
        Schema::create('service_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('service_type')->default('HOME_INTERNET'); // HOME_INTERNET, BUSINESS_INTERNET, PREPAID_WIFI, DEDICATED, ENTERPRISE
            $table->string('status')->default('PENDING'); // PENDING, ACTIVE, SUSPENDED, DISCONNECTED, TERMINATED, TRANSFERRED, ARCHIVED
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->foreignId('primary_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('service_username')->nullable(); // PPPoE / Radius identifier
            $table->string('circuit_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Service Locations (Physical installation locations per service account)
        Schema::create('service_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_account_id')->constrained('service_accounts')->cascadeOnDelete();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('service_area_id')->nullable()->constrained('service_areas')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('landmark')->nullable();
            $table->boolean('is_current')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamps();
        });

        // Subscriptions (Active commercial contract and plan assigned to a service account)
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_number')->unique();
            $table->foreignId('service_account_id')->constrained('service_accounts')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('service_packages');
            $table->foreignId('package_version_id')->constrained('service_package_versions');
            $table->foreignId('installation_id')->nullable()->constrained('installation_work_orders')->nullOnDelete();

            // Commercial Package Snapshot
            $table->string('package_name_snapshot');
            $table->integer('download_speed_snapshot');
            $table->integer('upload_speed_snapshot');
            $table->decimal('monthly_price_snapshot', 15, 2);
            $table->string('billing_cycle_snapshot')->default('MONTHLY');
            $table->integer('contract_duration_months')->default(12);

            $table->string('status')->default('PENDING'); // PENDING, ACTIVE, GRACE, SUSPENDED, RECONNECTION_PENDING, TERMINATION_PENDING, TERMINATED, CANCELLED
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('next_billing_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Subscription Status Histories
        Schema::create('subscription_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Service Requests (Upgrades, Downgrades, Relocations, Suspensions, Reconnections, Terminations, Ownership Transfers)
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('service_account_id')->constrained('service_accounts')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('request_type'); // PACKAGE_UPGRADE, PACKAGE_DOWNGRADE, RELOCATION, SUSPENSION, RECONNECTION, TERMINATION, OWNERSHIP_TRANSFER, EQUIPMENT_REPLACEMENT
            $table->string('priority')->default('NORMAL');
            $table->string('status')->default('SUBMITTED'); // DRAFT, SUBMITTED, UNDER_REVIEW, APPROVED, REJECTED, SCHEDULED, IN_PROGRESS, COMPLETED, CANCELLED

            $table->json('request_payload')->nullable(); // Target package, new location coords, target customer, etc.
            $table->text('reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // Service Contracts
        Schema::create('service_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('service_account_id')->constrained('service_accounts')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('monthly_fee', 15, 2);
            $table->decimal('deposit_amount', 15, 2)->default(0.00);
            $table->string('status')->default('ACTIVE'); // ACTIVE, EXPIRED, TERMINATED, RENEWED
            $table->string('contract_file_path')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_contracts');
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('subscription_status_histories');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('service_locations');
        Schema::dropIfExists('service_accounts');
    }
};
