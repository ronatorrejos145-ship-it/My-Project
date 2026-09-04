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
        // Service Categories
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category_type', 50)->default('BROADBAND'); // HOME, BUSINESS, CORPORATE, PREPAID, PUBLIC_WIFI, DEDICATED, FIBER, WIRELESS, OTHER
            $table->string('icon_path')->nullable();
            $table->integer('display_order')->default(0);
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Enhance service_packages table
        Schema::table('service_packages', function (Blueprint $table) {
            $table->foreignId('service_category_id')->nullable()->after('package_code')->constrained('service_categories')->nullOnDelete();
            $table->string('short_name', 100)->nullable()->after('name');
            $table->string('technology', 50)->default('FIBER')->after('package_type'); // FIBER, WIRELESS, FIXED_WIRELESS, FTTH, FTTB, RADIO, MESH, HOTSPOT, HYBRID
            $table->integer('speed_guaranteed')->default(0)->after('upload_speed');
            $table->integer('burst_speed')->default(0)->after('speed_guaranteed');

            $table->boolean('fup_enabled')->default(false)->after('fair_use_policy');
            $table->integer('fup_threshold_gb')->default(0)->after('fup_enabled');
            $table->string('fup_action', 50)->default('NO_ACTION')->after('fup_threshold_gb'); // NO_ACTION, THROTTLE, TEMPORARY_RESTRICTION
            $table->integer('data_allowance_gb')->default(0)->after('fup_action'); // 0 = Unlimited

            $table->decimal('reconnection_fee', 15, 2)->default(0.00)->after('deposit_amount');
            $table->decimal('relocation_fee', 15, 2)->default(0.00)->after('reconnection_fee');

            $table->boolean('public_visibility')->default(true)->after('notes');
            $table->boolean('featured')->default(false)->after('public_visibility');
            $table->integer('display_order')->default(0)->after('featured');
            $table->text('terms')->nullable()->after('display_order');

            $table->string('approval_status', 30)->default('APPROVED')->after('status'); // DRAFT, PENDING_APPROVAL, APPROVED, REJECTED
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        // Enhance service_package_versions table
        Schema::table('service_package_versions', function (Blueprint $table) {
            $table->string('version_name', 100)->nullable()->after('version_number');
            $table->integer('guaranteed_speed')->default(0)->after('upload_speed');
            $table->decimal('reconnection_fee', 15, 2)->default(0.00)->after('deposit_amount');
            $table->decimal('relocation_fee', 15, 2)->default(0.00)->after('reconnection_fee');
            $table->decimal('equipment_fee', 15, 2)->default(0.00)->after('relocation_fee');
            $table->integer('contract_period_months')->default(24)->after('equipment_fee');
            $table->integer('grace_period_days')->default(3)->after('contract_period_months');

            $table->foreignId('created_by')->nullable()->after('change_reason')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        // Enhance package_features table
        Schema::table('package_features', function (Blueprint $table) {
            $table->string('feature_type', 30)->default('BOOLEAN')->after('name'); // BOOLEAN, TEXT, NUMBER, AMOUNT, PERCENTAGE, LIMIT
            $table->string('default_value')->nullable()->after('feature_type');
        });

        // Promotions & Campaigns
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('promo_type', 50)->default('DISCOUNT'); // DISCOUNT, FREE_INSTALLATION, FIRST_MONTH_FREE, GIFT
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('discount_percentage', 8, 4)->default(0.0000);
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->integer('usage_limit')->default(0); // 0 = unlimited
            $table->integer('used_count')->default(0);
            $table->integer('customer_usage_limit')->default(1);
            $table->boolean('public_visibility')->default(true);
            $table->boolean('stackable_flag')->default(false);
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Availability Matrix Pivot Tables
        Schema::create('service_package_branch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')->constrained('service_packages')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['service_package_id', 'branch_id']);
        });

        Schema::create('service_package_service_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')->constrained('service_packages')->onDelete('cascade');
            $table->foreignId('service_area_id')->constrained('service_areas')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['service_package_id', 'service_area_id']);
        });

        Schema::create('service_package_customer_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')->constrained('service_packages')->onDelete('cascade');
            $table->string('customer_type', 50); // RESIDENTIAL, BUSINESS, CORPORATE, etc.
            $table->timestamps();

            $table->unique(['service_package_id', 'customer_type']);
        });

        Schema::create('promotion_service_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->onDelete('cascade');
            $table->foreignId('service_package_id')->constrained('service_packages')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['promotion_id', 'service_package_id']);
        });

        // Package Equipment Requirements
        Schema::create('package_equipment_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('service_packages')->onDelete('cascade');
            $table->foreignId('asset_model_id')->constrained('asset_models')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_included')->default(true); // Included in plan vs additional charge
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['package_id', 'asset_model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_equipment_requirements');
        Schema::dropIfExists('promotion_service_package');
        Schema::dropIfExists('service_package_customer_type');
        Schema::dropIfExists('service_package_service_area');
        Schema::dropIfExists('service_package_branch');
        Schema::dropIfExists('promotions');

        Schema::table('package_features', function (Blueprint $table) {
            $table->dropColumn(['feature_type', 'default_value']);
        });

        Schema::table('service_package_versions', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['version_name', 'guaranteed_speed', 'reconnection_fee', 'relocation_fee', 'equipment_fee', 'contract_period_months', 'grace_period_days', 'created_by', 'approved_by', 'approved_at']);
        });

        Schema::table('service_packages', function (Blueprint $table) {
            $table->dropForeign(['service_category_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'service_category_id', 'short_name', 'technology', 'speed_guaranteed', 'burst_speed',
                'fup_enabled', 'fup_threshold_gb', 'fup_action', 'data_allowance_gb',
                'reconnection_fee', 'relocation_fee', 'public_visibility', 'featured', 'display_order',
                'terms', 'approval_status', 'approved_by', 'approved_at'
            ]);
        });

        Schema::dropIfExists('service_categories');
    }
};
