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
        // 1. Collection Policies Master
        Schema::create('collection_policies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('customer_type', 50)->default('ALL')->index(); // RESIDENTIAL, BUSINESS, ALL
            $table->decimal('min_overdue_amount', 15, 2)->default(100.00);
            $table->integer('min_days_overdue')->default(1);
            $table->integer('grace_period_days')->default(3);
            $table->integer('suspension_threshold_days')->default(15);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Collection Policy Steps
        Schema::create('collection_policy_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained('collection_policies')->onDelete('cascade');
            $table->integer('step_number')->default(1);
            $table->integer('trigger_days_overdue');
            $table->string('action_type', 50); // SMS_REMINDER, EMAIL_REMINDER, CALL, WARNING, SUSPENSION_WARNING
            $table->string('template_name')->nullable();
            $table->boolean('is_automatic')->default(true);
            $table->timestamps();
        });

        // 3. Collection Accounts / Profiles
        Schema::create('collection_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->string('delinquency_status', 50)->default('CURRENT')->index(); // CURRENT, DUE, GRACE_PERIOD, OVERDUE, COLLECTION_WARNING, SUSPENSION_ELIGIBLE, SUSPENDED, RECONNECTED, WRITTEN_OFF
            $table->date('oldest_unpaid_invoice_date')->nullable();
            $table->string('oldest_unpaid_invoice_number', 50)->nullable();
            $table->decimal('total_outstanding_amount', 15, 2)->default(0.00);
            $table->decimal('overdue_amount', 15, 2)->default(0.00);
            $table->integer('days_overdue')->default(0);
            $table->integer('overdue_invoice_count')->default(0);
            $table->date('last_payment_date')->nullable();
            $table->decimal('last_payment_amount', 15, 2)->nullable();
            $table->string('last_collection_action')->nullable();
            $table->date('next_collection_action_date')->nullable();
            $table->date('suspension_eligibility_date')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('reconnected_at')->nullable();
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('risk_level', 30)->default('LOW')->index(); // LOW, MEDIUM, HIGH, CRITICAL
            $table->boolean('is_exempt_from_suspension')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Collection Actions Log
        Schema::create('collection_actions', function (Blueprint $table) {
            $table->id();
            $table->string('action_number', 50)->unique();
            $table->foreignId('collection_account_id')->constrained('collection_accounts')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('action_type', 50); // SMS, EMAIL, PHONE, WARNING, SUSPENSION_WARNING
            $table->foreignId('collector_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('action_at');
            $table->string('result_status', 30)->default('COMPLETED');
            $table->text('notes')->nullable();
            $table->date('next_action_date')->nullable();
            $table->timestamps();
        });

        // 5. Collection Contact History
        Schema::create('collection_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_account_id')->constrained('collection_accounts')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('collector_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('contact_channel', 30)->default('PHONE'); // PHONE, EMAIL, SMS, IN_PERSON
            $table->string('contacted_person')->nullable();
            $table->string('result', 50); // REACHED, NO_ANSWER, PROMISED_PAYMENT, REFUSED_PAYMENT
            $table->date('promised_payment_date')->nullable();
            $table->decimal('promised_payment_amount', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Collection Notes
        Schema::create('collection_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_account_id')->constrained('collection_accounts')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note_type', 30)->default('GENERAL');
            $table->text('content');
            $table->boolean('is_private')->default(true);
            $table->timestamps();
        });

        // 7. Promises to Pay
        Schema::create('promises_to_pay', function (Blueprint $table) {
            $table->id();
            $table->string('promise_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->foreignId('collection_account_id')->nullable()->constrained('collection_accounts')->nullOnDelete();
            $table->decimal('promised_amount', 15, 2)->default(0.00);
            $table->date('promised_date');
            $table->string('status', 30)->default('ACTIVE')->index(); // PENDING, ACTIVE, FULFILLED, BROKEN, CANCELLED, EXPIRED
            $table->decimal('fulfilled_amount', 15, 2)->default(0.00);
            $table->timestamp('fulfilled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 8. Payment Arrangements
        Schema::create('payment_arrangements', function (Blueprint $table) {
            $table->id();
            $table->string('arrangement_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('down_payment_amount', 15, 2)->default(0.00);
            $table->decimal('installment_amount', 15, 2)->default(0.00);
            $table->string('installment_frequency', 30)->default('MONTHLY'); // WEEKLY, BIWEEKLY, MONTHLY
            $table->integer('total_installments')->default(1);
            $table->integer('paid_installments')->default(0);
            $table->date('start_date');
            $table->integer('due_day_of_month')->default(1);
            $table->decimal('remaining_balance', 15, 2)->default(0.00);
            $table->string('status', 30)->default('PENDING_APPROVAL')->index(); // REQUESTED, PENDING_APPROVAL, APPROVED, ACTIVE, COMPLETED, DEFAULTED, CANCELLED
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 9. Payment Arrangement Installments Schedule
        Schema::create('payment_arrangement_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arrangement_id')->constrained('payment_arrangements')->onDelete('cascade');
            $table->integer('installment_number');
            $table->date('due_date');
            $table->decimal('amount_due', 15, 2)->default(0.00);
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->string('status', 30)->default('PENDING')->index(); // PENDING, PAID, OVERDUE
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->timestamps();
        });

        // 10. Suspension Exemptions / Holds
        Schema::create('suspension_exemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->text('reason');
            $table->date('start_date');
            $table->date('expiry_date')->nullable();
            $table->string('status', 30)->default('ACTIVE')->index(); // ACTIVE, EXPIRED, CANCELLED
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 11. Suspension Requests Queue
        Schema::create('suspension_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->text('reason');
            $table->decimal('delinquency_amount', 15, 2)->default(0.00);
            $table->integer('days_overdue')->default(0);
            $table->string('approval_status', 30)->default('APPROVED')->index(); // PENDING_APPROVAL, APPROVED, REJECTED, CANCELLED
            $table->string('network_action_status', 30)->default('PENDING')->index(); // PENDING, QUEUED, PROCESSING, COMPLETED, FAILED, NOT_CONFIGURED
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->text('result_notes')->nullable();
            $table->timestamps();
        });

        // 12. Suspension Executions Log (Network Contract)
        Schema::create('suspension_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suspension_request_id')->constrained('suspension_requests')->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('action_type', 30)->default('SUSPEND');
            $table->string('provider', 50)->default('MANUAL'); // ROUTER, RADIUS, MIKROTIK, MANUAL
            $table->string('status', 30)->default('SUCCESS');
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('executed_at');
            $table->timestamps();
        });

        // 13. Reconnection Requests Queue
        Schema::create('reconnection_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->foreignId('suspension_request_id')->nullable()->constrained('suspension_requests')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->decimal('amount_remaining', 15, 2)->default(0.00);
            $table->decimal('reconnection_fee', 15, 2)->default(0.00);
            $table->boolean('reconnection_fee_waived')->default(false);
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status', 30)->default('APPROVED')->index(); // REQUESTED, PENDING_APPROVAL, APPROVED, REJECTED
            $table->string('network_action_status', 30)->default('PENDING')->index(); // PENDING, QUEUED, PROCESSING, COMPLETED, FAILED, NOT_CONFIGURED
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 14. Reconnection Executions Log
        Schema::create('reconnection_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconnection_request_id')->constrained('reconnection_requests')->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('action_type', 30)->default('RECONNECT');
            $table->string('provider', 50)->default('MANUAL');
            $table->string('status', 30)->default('SUCCESS');
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('executed_at');
            $table->timestamps();
        });

        // 15. Write-Off Requests Queue
        Schema::create('write_off_requests', function (Blueprint $table) {
            $table->id();
            $table->string('write_off_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->text('reason');
            $table->string('accounting_reference', 100)->nullable();
            $table->string('status', 30)->default('PENDING_APPROVAL')->index(); // REQUESTED, PENDING_APPROVAL, APPROVED, POSTED, REJECTED, REVERSED
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('ledger_transaction_id')->nullable()->constrained('ledger_transactions')->nullOnDelete();
            $table->timestamps();
        });

        // 16. Delinquency State Machine Audit History
        Schema::create('delinquency_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->string('previous_status', 50)->nullable();
            $table->string('new_status', 50)->index();
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delinquency_histories');
        Schema::dropIfExists('write_off_requests');
        Schema::dropIfExists('reconnection_executions');
        Schema::dropIfExists('reconnection_requests');
        Schema::dropIfExists('suspension_executions');
        Schema::dropIfExists('suspension_requests');
        Schema::dropIfExists('suspension_exemptions');
        Schema::dropIfExists('payment_arrangement_installments');
        Schema::dropIfExists('payment_arrangements');
        Schema::dropIfExists('promises_to_pay');
        Schema::dropIfExists('collection_notes');
        Schema::dropIfExists('collection_contacts');
        Schema::dropIfExists('collection_actions');
        Schema::dropIfExists('collection_accounts');
        Schema::dropIfExists('collection_policy_steps');
        Schema::dropIfExists('collection_policies');
    }
};
