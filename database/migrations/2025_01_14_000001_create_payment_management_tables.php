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
        // Master Payments Table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();

            $table->string('payment_method_code', 50)->default('CASH'); // CASH, BANK_TRANSFER, GCASH, MAYA, CARD, ONLINE_GATEWAY
            $table->string('payment_channel', 50)->default('CASHIER'); // CASHIER, ONLINE_CHECKOUT, BANK_DEPOSIT, WEBHOOK

            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('PHP');

            $table->date('payment_date');
            $table->timestamp('received_at')->useCurrent();

            $table->string('reference_number')->nullable(); // Customer or Bank Reference
            $table->string('external_reference')->nullable(); // External Gateway Reference
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_provider')->nullable(); // GCASH, MAYA, PAY_MONGO, STRIPE, GENERIC

            $table->string('status')->default('PENDING'); // INITIATED, PENDING, VERIFIED, ALLOCATED, POSTED, FAILED, CANCELLED, EXPIRED, REVERSED
            $table->string('verification_status')->default('NOT_REQUIRED'); // NOT_REQUIRED, PENDING, VERIFIED, REJECTED
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->text('failure_reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->string('idempotency_key')->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('posted_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index(['service_account_id', 'status']);
            $table->index('reference_number');
            $table->index('gateway_transaction_id');
        });

        // Payment Sessions for Online Checkout
        Schema::create('payment_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('PHP');
            $table->string('gateway_provider');
            $table->string('checkout_url')->nullable();
            $table->string('status')->default('CREATED'); // CREATED, PENDING, COMPLETED, FAILED, EXPIRED, CANCELLED
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Webhook Events Log
        Schema::create('payment_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('event_id');
            $table->string('event_type')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->string('status')->default('PROCESSED'); // PROCESSED, DUPLICATE_SKIPPED, FAILED
            $table->string('idempotency_key')->unique();
            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();
        });

        // Manual Bank Deposit Verifications
        Schema::create('payment_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('verifier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('PENDING'); // PENDING, VERIFIED, REJECTED
            $table->decimal('amount_confirmed', 15, 2);
            $table->string('reference_confirmed')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // Payment Proof Attachments
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('checksum')->nullable();
            $table->timestamps();
        });

        // Cashier Sessions & Drawer Reconciliation
        Schema::create('cashier_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_number')->unique();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_cash', 15, 2)->default(0.00);
            $table->decimal('expected_cash', 15, 2)->default(0.00);
            $table->decimal('actual_cash', 15, 2)->default(0.00);
            $table->decimal('variance', 15, 2)->default(0.00);
            $table->string('status')->default('OPEN'); // OPEN, CLOSING, CLOSED, RECONCILED
            $table->text('variance_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Bank Statement Transactions Import
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('account_number')->nullable();
            $table->date('transaction_date');
            $table->string('reference_number')->nullable()->index();
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('UNMATCHED'); // UNMATCHED, MATCHED, DUPLICATE, IGNORED
            $table->foreignId('matched_payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->timestamp('matched_at')->nullable();
            $table->timestamps();
        });

        // Expand Receipts Table if needed
        if (Schema::hasTable('receipts')) {
            Schema::table('receipts', function (Blueprint $table) {
                if (!Schema::hasColumn('receipts', 'payment_id')) {
                    $table->foreignId('payment_id')->nullable()->after('customer_id')->constrained('payments')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('receipts')) {
            Schema::table('receipts', function (Blueprint $table) {
                if (Schema::hasColumn('receipts', 'payment_id')) {
                    $table->dropForeign(['payment_id']);
                    $table->dropColumn('payment_id');
                }
            });
        }

        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('cashier_sessions');
        Schema::dropIfExists('payment_proofs');
        Schema::dropIfExists('payment_verifications');
        Schema::dropIfExists('payment_webhook_events');
        Schema::dropIfExists('payment_sessions');
        Schema::dropIfExists('payments');
    }
};
