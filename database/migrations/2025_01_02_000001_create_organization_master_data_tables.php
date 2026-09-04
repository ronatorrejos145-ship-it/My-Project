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
        // Companies
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('legal_name');
            $table->string('trade_name')->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->string('tax_identifier', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Branches
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('restrict');
            $table->string('code', 50);
            $table->string('name');
            $table->string('branch_type', 50)->default('BRANCH')->index();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->foreignId('manager_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
        });

        // Positions
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->foreignId('department_id')->constrained('departments')->onDelete('restrict');
            $table->text('description')->nullable();
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Enhance employees with branch and position foreign keys
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('department_id')->constrained('branches')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->after('branch_id')->constrained('positions')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->after('position_id')->constrained('employees')->nullOnDelete();
            $table->date('hire_date')->nullable()->after('employment_status');
            $table->date('termination_date')->nullable()->after('hire_date');
            $table->json('emergency_contact')->nullable()->after('termination_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn(['branch_id', 'position_id', 'supervisor_id', 'hire_date', 'termination_date', 'emergency_contact']);
        });

        Schema::dropIfExists('positions');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('companies');
    }
};
