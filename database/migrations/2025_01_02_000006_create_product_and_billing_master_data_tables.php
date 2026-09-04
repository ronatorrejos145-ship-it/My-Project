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
        // Billing Cycles
        Schema::create('billing_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->integer('interval')->default(1);
            $table->string('interval_unit', 30)->default('MONTH'); // DAY, WEEK, MONTH, YEAR
            $table->text('description')->nullable();
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
        });

        // Taxes
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->decimal('rate', 8, 4)->default(0.0000); // e.g., 12.0000 for 12% VAT
            $table->string('tax_type', 50)->default('PERCENTAGE'); // PERCENTAGE, FIXED
            $table->boolean('is_inclusive')->default(false);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
        });

        // Payment Methods
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('method_type', 50)->default('CASH'); // CASH, BANK_TRANSFER, GCASH, MAYA, CARD, ONLINE_GATEWAY, CHECK, OTHER
            $table->string('provider')->nullable();
            $table->boolean('requires_reference')->default(false);
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
        });

        // Service Packages
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('package_type', 50)->default('RESIDENTIAL')->index(); // RESIDENTIAL, BUSINESS, CORPORATE, PREPAID, POSTPAID, CUSTOM
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->integer('download_speed')->default(0);
            $table->integer('upload_speed')->default(0);
            $table->string('speed_unit', 20)->default('Mbps');
            $table->decimal('base_price', 15, 2)->default(0.00);
            $table->decimal('installation_fee', 15, 2)->default(0.00);
            $table->decimal('activation_fee', 15, 2)->default(0.00);
            $table->decimal('deposit_amount', 15, 2)->default(0.00);
            $table->foreignId('billing_cycle_id')->nullable()->constrained('billing_cycles')->nullOnDelete();
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->integer('grace_period_days')->default(3);
            $table->integer('contract_period_months')->default(12);
            $table->text('fair_use_policy')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Package Versions
        Schema::create('service_package_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('service_packages')->onDelete('cascade');
            $table->integer('version_number')->default(1);
            $table->dateTime('effective_from');
            $table->dateTime('effective_until')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('installation_fee', 15, 2)->default(0.00);
            $table->decimal('activation_fee', 15, 2)->default(0.00);
            $table->decimal('deposit_amount', 15, 2)->default(0.00);
            $table->integer('download_speed');
            $table->integer('upload_speed');
            $table->string('speed_unit', 20)->default('Mbps');
            $table->foreignId('billing_cycle_id')->nullable()->constrained('billing_cycles')->nullOnDelete();
            $table->string('status', 30)->default('ACTIVE');
            $table->text('change_reason')->nullable();
            $table->timestamps();

            $table->unique(['package_id', 'version_number']);
        });

        // Package Features
        Schema::create('package_features', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Pivot: Package Features - Service Package
        Schema::create('package_feature_service_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')->constrained('service_packages')->onDelete('cascade');
            $table->foreignId('package_feature_id')->constrained('package_features')->onDelete('cascade');
            $table->string('feature_value')->nullable();
            $table->timestamps();
        });

        // Discounts and Promotions Foundation
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('discount_type', 30)->default('PERCENTAGE'); // PERCENTAGE, FIXED
            $table->decimal('value', 15, 2)->default(0.00);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
        Schema::dropIfExists('package_feature_service_package');
        Schema::dropIfExists('package_features');
        Schema::dropIfExists('service_package_versions');
        Schema::dropIfExists('service_packages');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('billing_cycles');
    }
};
