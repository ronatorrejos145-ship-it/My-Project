<?php

namespace Database\Seeders;

use App\Models\CannedResponse;
use App\Models\Customer;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\SlaPolicy;
use App\Models\User;
use App\Services\ComplaintService;
use App\Services\TicketService;
use Illuminate\Database\Seeder;

class Phase18CustomerServiceSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::where('status', 'ACTIVE')->first();
        if (!$customer) return;

        $user = User::first();
        $ticketService = app(TicketService::class);
        $complaintService = app(ComplaintService::class);

        // 1. Seed SLA Policies
        SlaPolicy::firstOrCreate(
            ['name' => 'Critical Technical Incident Policy'],
            [
                'priority' => 'CRITICAL',
                'first_response_minutes' => 15,
                'resolution_minutes' => 120,
                'business_hours_only' => false,
                'is_active' => true,
            ]
        );

        SlaPolicy::firstOrCreate(
            ['name' => 'Standard Helpdesk Policy'],
            [
                'priority' => 'NORMAL',
                'first_response_minutes' => 240,
                'resolution_minutes' => 1440,
                'business_hours_only' => true,
                'is_active' => true,
            ]
        );

        // 2. Seed Ticket
        $ticket = $ticketService->createTicket(
            customer: $customer,
            subject: 'WiFi Router Loss of Optical Signal',
            description: 'Red LOS LED light blinking on optical network terminal since morning.',
            category: 'TECHNICAL',
            ticketType: 'INCIDENT',
            priority: 'HIGH',
            source: 'CUSTOMER_PORTAL'
        );

        $ticketService->addMessage(
            ticket: $ticket,
            message: 'Checking fiber drop wire continuity in local distribution box.',
            visibility: 'INTERNAL_ONLY',
            userId: $user?->id,
            authorType: 'AGENT'
        );

        $ticketService->addMessage(
            ticket: $ticket,
            message: 'Hello! A technician has been dispatched to check your line.',
            visibility: 'CUSTOMER_VISIBLE',
            userId: $user?->id,
            authorType: 'AGENT'
        );

        // 3. Seed Complaint
        $complaintService->createComplaint(
            customer: $customer,
            description: 'Dissatisfied with response time on recurring loss of signal issue.',
            category: 'SERVICE_QUALITY',
            severity: 'HIGH',
            ticket: $ticket,
            assignedOfficerId: $user?->id
        );

        // 4. Seed KB & Canned Response
        $cat = KnowledgeCategory::firstOrCreate(['name' => 'Troubleshooting', 'slug' => 'troubleshooting']);
        KnowledgeArticle::create([
            'title' => 'How to Check Optical Light Status on Fiber ONU',
            'slug' => 'check-optical-light-status',
            'category_id' => $cat->id,
            'content' => 'If the LOS light is blinking red, check that the yellow fiber optic patch cable is securely connected...',
            'visibility' => 'CUSTOMER',
            'is_published' => true,
            'author_id' => $user?->id,
        ]);

        CannedResponse::create([
            'title' => 'Fiber Dispatch Notification',
            'category' => 'TECHNICAL',
            'content' => 'Our technical field team has been notified and scheduled for on-site inspection.',
            'is_active' => true,
        ]);
    }
}
