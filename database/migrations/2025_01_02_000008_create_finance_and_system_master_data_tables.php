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
        // Chart of Accounts
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code', 50)->unique();
            $table->string('account_name');
            $table->string('account_type', 50)->index(); // ASSET, LIABILITY, EQUITY, REVENUE, EXPENSE
            $table->foreignId('parent_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('normal_balance', 10)->default('DEBIT'); // DEBIT, CREDIT
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Transaction Types Master Data
        Schema::create('transaction_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('category', 50)->default('BILLING'); // BILLING, PAYMENT, ADJUSTMENT, REFUND, EXPENSE
            $table->foreignId('default_debit_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('default_credit_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Concurrency-Safe Number Sequences
        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // CUSTOMER, ACCOUNT, INVOICE, RECEIPT, PAYMENT, TICKET, WORK_ORDER, ASSET, TOOL, PURCHASE_ORDER, EMPLOYEE, etc.
            $table->string('name');
            $table->string('prefix', 20)->default('');
            $table->string('suffix', 20)->default('');
            $table->unsignedBigInteger('current_number')->default(0);
            $table->integer('padding')->default(6);
            $table->string('reset_period', 20)->default('NEVER'); // NEVER, YEARLY, MONTHLY, DAILY
            $table->string('last_reset_date', 10)->nullable();
            $table->boolean('is_branch_aware')->default(false);
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('number_sequences');
        Schema::dropIfExists('transaction_types');
        Schema::dropIfExists('accounts');
    }
};
