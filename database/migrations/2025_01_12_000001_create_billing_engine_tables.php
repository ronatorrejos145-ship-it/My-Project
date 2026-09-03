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
        // Billing Profiles (Subscriber-level billing rules and cycles)
        Schema::create('billing_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_account_id')->constrained('service_accounts')->cascadeOnDelete();
            $table->string('billing_method')->default('POSTPAID'); // PREPAID, POSTPAID
            $table->string('billing_cycle')->default('MONTHLY'); // DAILY, WEEKLY, MONTHLY, CUSTOM
            $table->integer('billing_day')->default(1); // 1-31
            $table->date('billing_start_date');
            $table->date('next_billing_date');
            $table->integer('due_days')->default(15);
            $table->integer('grace_days')->default(3);
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->string('currency', 3)->default('PHP');
            $table->string('status')->default('ACTIVE'); // ACTIVE, SUSPENDED, HOLD, FREEZE
            $table->boolean('auto_bill_enabled')->default(true);
            $table->boolean('billing_hold')->default(false);
            $table->string('billing_hold_reason')->nullable();
            $table->date('billing_hold_until')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Billing Periods
        Schema::create('billing_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_profile_id')->constrained('billing_profiles')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->date('billing_date');
            $table->date('due_date');
            $table->date('grace_date');
            $table->string('status')->default('OPEN'); // OPEN, PREVIEW, PROCESSING, GENERATED, FINALIZED, FAILED, CANCELLED
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->unsignedBigInteger('billing_run_id')->nullable();
            $table->timestamps();

            $table->index(['billing_profile_id', 'period_start', 'period_end'], 'period_boundary_index');
        });

        // Billable Events (Mid-cycle activations, upgrades, relocations, fees)
        Schema::create('billable_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // SUBSCRIPTION_ACTIVATED, PACKAGE_UPGRADED, PACKAGE_DOWNGRADED, SERVICE_RELOCATED, SERVICE_RECONNECTED, ONE_TIME_FEE, DEPOSIT_REQUIRED
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_account_id')->constrained('service_accounts')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->date('event_date');
            $table->date('effective_date');
            $table->string('source_module')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->decimal('quantity', 15, 2)->default(1.00);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('calculated_amount', 15, 2)->default(0.00);
            $table->json('metadata')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, PROCESSED, SKIPPED, CANCELLED
            $table->timestamp('processed_at')->nullable();
            $table->string('idempotency_key')->unique();
            $table->timestamps();
        });

        // Billing Runs (Batch billing execution records)
        Schema::create('billing_runs', function (Blueprint $table) {
            $table->id();
            $table->string('run_number')->unique();
            $table->date('billing_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->string('billing_cycle')->default('MONTHLY');
            $table->string('status')->default('DRAFT'); // DRAFT, PREVIEW, VALIDATING, PROCESSING, COMPLETED, COMPLETED_WITH_ERRORS, FAILED, CANCELLED
            $table->integer('total_accounts')->default(0);
            $table->integer('eligible_accounts')->default(0);
            $table->integer('skipped_accounts')->default(0);
            $table->integer('successful_accounts')->default(0);
            $table->integer('failed_accounts')->default(0);
            $table->integer('total_charges')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('error_summary')->nullable();
            $table->timestamps();
        });

        // Billable Charges (Authoritative deterministic charge entries ready for Phase 13 Invoicing)
        Schema::create('billable_charges', function (Blueprint $table) {
            $table->id();
            $table->string('charge_number')->unique();
            $table->foreignId('billing_run_id')->nullable()->constrained('billing_runs')->nullOnDelete();
            $table->foreignId('billing_period_id')->nullable()->constrained('billing_periods')->nullOnDelete();
            $table->foreignId('billing_profile_id')->constrained('billing_profiles')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_account_id')->constrained('service_accounts')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('service_packages')->nullOnDelete();
            $table->foreignId('package_version_id')->nullable()->constrained('service_package_versions')->nullOnDelete();

            $table->string('charge_type'); // RECURRING, ONE_TIME, PRORATED, ACTIVATION, INSTALLATION, RELOCATION, RECONNECTION, DEPOSIT, ADJUSTMENT
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('description');

            $table->decimal('quantity', 15, 2)->default(1.00);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('taxable_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('PHP');

            $table->date('service_period_start')->nullable();
            $table->date('service_period_end')->nullable();
            $table->date('effective_date');

            $table->string('status')->default('CHARGED'); // BILLABLE, PREVIEWED, CHARGED, INVOICED, ON_HOLD, EXCEPTION, REVERSED
            $table->string('idempotency_key')->unique();
            $table->json('calculation_snapshot')->nullable(); // Package price, proration basis, tax rule, etc.
            $table->json('metadata')->nullable();

            $table->timestamp('generated_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['service_account_id', 'status']);
            $table->index(['billing_period_id', 'status']);
        });

        // Billing Exceptions
        Schema::create('billing_exceptions', function (Blueprint $table) {
            $table->id();
            $table->string('exception_number')->unique();
            $table->foreignId('billing_run_id')->nullable()->constrained('billing_runs')->nullOnDelete();
            $table->foreignId('service_account_id')->constrained('service_accounts')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('severity')->default('ERROR'); // WARNING, ERROR, CRITICAL
            $table->string('type'); // MISSING_PRICE, INVALID_SUBSCRIPTION, CALCULATION_FAILURE, BILLING_HOLD, DUPLICATE_EVENT
            $table->text('message');
            $table->json('details')->nullable();
            $table->string('status')->default('OPEN'); // OPEN, INVESTIGATING, RESOLVED, IGNORED
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->timestamps();
        });

        // Billing Adjustments
        Schema::create('billing_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_account_id')->constrained('service_accounts')->cascadeOnDelete();
            $table->foreignId('billable_charge_id')->nullable()->constrained('billable_charges')->nullOnDelete();
            $table->string('adjustment_type')->default('CREDIT'); // CREDIT, DEBIT, CORRECTION
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_adjustments');
        Schema::dropIfExists('billing_exceptions');
        Schema::dropIfExists('billable_charges');
        Schema::dropIfExists('billing_runs');
        Schema::dropIfExists('billable_events');
        Schema::dropIfExists('billing_periods');
        Schema::dropIfExists('billing_profiles');
    }
};
