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
        // Ticket Categories
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Ticket Priorities
        Schema::create('ticket_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->integer('level')->default(1); // 1 = Low, 2 = Medium, 3 = High, 4 = Urgent, 5 = Critical
            $table->string('color_code', 20)->default('#3B82F6');
            $table->timestamps();
        });

        // Ticket Statuses
        Schema::create('ticket_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });

        // SLA Definitions
        Schema::create('sla_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
            $table->foreignId('priority_id')->nullable()->constrained('ticket_priorities')->nullOnDelete();
            $table->integer('response_time_minutes')->default(60);
            $table->integer('resolution_time_minutes')->default(240);
            $table->string('business_hours', 100)->default('24/7');
            $table->integer('escalation_level')->default(1);
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();
        });

        // Work Orders Foundation
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number', 50)->unique();
            $table->string('work_order_type', 50)->index(); // INSTALLATION, MAINTENANCE, REPAIR, RELOCATION, INSPECTION, SURVEY
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('service_area_id')->nullable()->constrained('service_areas')->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('priority', 30)->default('MEDIUM')->index();
            $table->string('status', 30)->default('PENDING')->index(); // PENDING, ASSIGNED, SCHEDULED, EN_ROUTE, ON_SITE, IN_PROGRESS, TESTING, COMPLETED, FAILED, CANCELLED
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('sla_definitions');
        Schema::dropIfExists('ticket_statuses');
        Schema::dropIfExists('ticket_priorities');
        Schema::dropIfExists('ticket_categories');
    }
};
