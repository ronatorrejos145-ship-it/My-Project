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
        // Invoices Master Table
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->foreignId('billing_period_id')->nullable()->constrained('billing_periods')->nullOnDelete();
            $table->foreignId('billing_run_id')->nullable()->constrained('billing_runs')->nullOnDelete();

            $table->date('invoice_date');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->date('due_date');
            $table->date('grace_date')->nullable();
            $table->string('currency', 3)->default('PHP');

            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('taxable_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->decimal('amount_due', 15, 2)->default(0.00);

            $table->string('status')->default('DRAFT'); // DRAFT, PREVIEW, GENERATED, FINALIZED, PARTIALLY_PAID, PAID, OVERDUE, VOID, CANCELLED
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index(['service_account_id', 'status']);
            $table->index('due_date');
        });

        // Invoice Line Items
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('charge_id')->nullable()->constrained('billable_charges')->nullOnDelete();
            $table->string('charge_type'); // RECURRING, ONE_TIME, PRORATED, ACTIVATION, INSTALLATION, RELOCATION, RECONNECTION, DEPOSIT, ADJUSTMENT
            $table->string('description');

            $table->decimal('quantity', 15, 2)->default(1.00);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('taxable_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('tax_rate', 8, 4)->default(0.0000);

            $table->date('service_period_start')->nullable();
            $table->date('service_period_end')->nullable();

            $table->foreignId('package_id')->nullable()->constrained('service_packages')->nullOnDelete();
            $table->foreignId('package_version_id')->nullable()->constrained('service_package_versions')->nullOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Authoritative Double-Entry Financial Ledger
        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();

            $table->string('transaction_type'); // INVOICE, PAYMENT, CREDIT, DEBIT, DISCOUNT, REBATE, REFUND, ADJUSTMENT, REVERSAL, PENALTY, DEPOSIT
            $table->date('transaction_date');
            $table->timestamp('posting_date')->useCurrent();

            $table->decimal('debit_amount', 15, 2)->default(0.00);
            $table->decimal('credit_amount', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2)->default(0.00); // debit - credit
            $table->string('currency', 3)->default('PHP');

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description');
            $table->string('status')->default('POSTED'); // PENDING, POSTED, REVERSED, CANCELLED

            $table->foreignId('reversal_of_id')->nullable()->constrained('ledger_transactions')->nullOnDelete();
            $table->json('metadata')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'posting_date']);
            $table->index(['service_account_id', 'posting_date']);
            $table->index(['reference_type', 'reference_id']);
        });

        // Account Balance Snapshots (Optimized cache derived strictly from ledger)
        Schema::create('account_balance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->decimal('total_debits', 15, 2)->default(0.00);
            $table->decimal('total_credits', 15, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->foreignId('last_transaction_id')->nullable()->constrained('ledger_transactions')->nullOnDelete();
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'service_account_id'], 'account_balance_unique');
        });

        // Receipts Foundation
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->string('payment_reference')->nullable();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('PHP');
            $table->string('payment_method')->default('CASH');
            $table->string('reference_number')->nullable();
            $table->string('status')->default('ISSUED'); // ISSUED, VOID, CANCELLED
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });

        // Payment Allocations Foundation (Multi-invoice payment matrix)
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->nullable()->constrained('receipts')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('allocated_amount', 15, 2);
            $table->timestamp('allocated_at')->useCurrent();
            $table->timestamps();
        });

        // Financial Reversals & Adjustments Log
        Schema::create('financial_reversals', function (Blueprint $table) {
            $table->id();
            $table->string('reversal_number')->unique();
            $table->foreignId('original_transaction_id')->constrained('ledger_transactions')->cascadeOnDelete();
            $table->foreignId('reversal_transaction_id')->nullable()->constrained('ledger_transactions')->nullOnDelete();
            $table->text('reason');
            $table->decimal('amount', 15, 2);
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reversed_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_reversals');
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('account_balance_snapshots');
        Schema::dropIfExists('ledger_transactions');
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
    }
};
