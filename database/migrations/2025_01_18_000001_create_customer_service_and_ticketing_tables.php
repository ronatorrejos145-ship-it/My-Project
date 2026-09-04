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
        // 1. SLA Policies Master
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('priority', 30)->default('NORMAL')->index(); // LOW, NORMAL, HIGH, URGENT, CRITICAL
            $table->integer('first_response_minutes')->default(60);
            $table->integer('resolution_minutes')->default(240);
            $table->boolean('business_hours_only')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Support Tickets Master
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->string('category', 50)->default('TECHNICAL')->index(); // TECHNICAL, BILLING, PAYMENT, INSTALLATION, ACCOUNT, OTHER
            $table->string('subcategory', 50)->nullable();
            $table->string('ticket_type', 50)->default('INCIDENT')->index(); // INCIDENT, SERVICE_REQUEST, COMPLAINT, QUESTION
            $table->string('priority', 30)->default('NORMAL')->index(); // LOW, NORMAL, HIGH, URGENT, CRITICAL
            $table->string('severity', 30)->default('MODERATE')->index(); // MINOR, MODERATE, MAJOR, SEVERE, CRITICAL
            $table->string('status', 30)->default('NEW')->index(); // NEW, OPEN, ASSIGNED, IN_PROGRESS, WAITING_CUSTOMER, WAITING_INTERNAL, ESCALATED, RESOLVED, CLOSED, REOPENED
            $table->string('source', 30)->default('CUSTOMER_PORTAL'); // CUSTOMER_PORTAL, WEB, PHONE, EMAIL, ADMIN
            $table->foreignId('assigned_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sla_policy_id')->nullable()->constrained('sla_policies')->nullOnDelete();
            $table->timestamp('first_response_due_at')->nullable();
            $table->timestamp('resolution_due_at')->nullable();
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('is_sla_breached')->default(false);
            $table->foreignId('merged_into_ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Ticket Messages / Conversations
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_type', 30)->default('CUSTOMER'); // CUSTOMER, AGENT, SYSTEM
            $table->string('visibility', 30)->default('CUSTOMER_VISIBLE')->index(); // CUSTOMER_VISIBLE, INTERNAL_ONLY, SYSTEM
            $table->text('message');
            $table->timestamps();
        });

        // 4. Ticket Message Attachments
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('ticket_message_id')->nullable()->constrained('ticket_messages')->nullOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type', 100);
            $table->integer('file_size_bytes');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Ticket Status Transition Audit Log
        Schema::create('ticket_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->string('previous_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 6. Customer Complaints
        Schema::create('customer_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->string('category', 50)->default('SERVICE_QUALITY')->index();
            $table->string('severity', 30)->default('HIGH')->index();
            $table->text('description');
            $table->text('root_cause_analysis')->nullable();
            $table->text('action_taken')->nullable();
            $table->string('status', 30)->default('RECEIVED')->index(); // RECEIVED, ACKNOWLEDGED, INVESTIGATING, ESCALATED, RESOLVED, CLOSED
            $table->foreignId('assigned_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // 7. Major Support Incidents
        Schema::create('support_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_number', 50)->unique();
            $table->string('title');
            $table->text('description');
            $table->string('severity', 30)->default('MAJOR')->index();
            $table->string('status', 30)->default('INVESTIGATING')->index(); // IDENTIFIED, INVESTIGATING, MITIGATING, RESOLVED, CLOSED
            $table->timestamp('started_at');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('lead_investigator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 8. Knowledge Base Categories & Articles
        Schema::create('knowledge_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained('knowledge_categories')->onDelete('cascade');
            $table->text('content');
            $table->string('visibility', 30)->default('CUSTOMER')->index(); // CUSTOMER, INTERNAL, BOTH
            $table->boolean('is_published')->default(true);
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 9. Canned Responses
        Schema::create('canned_responses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 50)->default('GENERAL');
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 10. CSAT Satisfaction Reviews
        Schema::create('ticket_satisfaction_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->integer('rating_score'); // 1 to 5
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_satisfaction_reviews');
        Schema::dropIfExists('canned_responses');
        Schema::dropIfExists('knowledge_articles');
        Schema::dropIfExists('knowledge_categories');
        Schema::dropIfExists('support_incidents');
        Schema::dropIfExists('customer_complaints');
        Schema::dropIfExists('ticket_status_histories');
        Schema::dropIfExists('ticket_attachments');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('sla_policies');
    }
};
