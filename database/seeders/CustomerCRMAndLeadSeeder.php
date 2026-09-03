<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\CustomerDocument;
use App\Models\CustomerNote;
use App\Models\CustomerTag;
use App\Models\CustomerActivity;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\ServicePackage;
use Illuminate\Database\Seeder;

class CustomerCRMAndLeadSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();
        $employee = Employee::first();
        $package = ServicePackage::first();

        // Customer Tags
        $tagVip = CustomerTag::firstOrCreate(['code' => 'VIP'], ['name' => 'VIP Account', 'color_code' => '#8B5CF6']);
        $tagBiz = CustomerTag::firstOrCreate(['code' => 'BUSINESS'], ['name' => 'Commercial Account', 'color_code' => '#3B82F6']);
        $tagNew = CustomerTag::firstOrCreate(['code' => 'NEW'], ['name' => 'New Subscriber', 'color_code' => '#10B981']);

        // Individual Customer
        $c1 = Customer::firstOrCreate(
            ['customer_number' => 'CUST-000001'],
            [
                'account_number' => 'ACC-000001',
                'first_name' => 'Maria',
                'middle_name' => 'Santos',
                'last_name' => 'Dela Cruz',
                'contact_person' => 'Maria Dela Cruz',
                'customer_type' => 'RESIDENTIAL',
                'status' => 'ACTIVE',
                'primary_phone' => '+63 917 123 4567',
                'email' => 'maria.delacruz@demo.local',
                'branch_id' => $branch?->id,
                'assigned_employee_id' => $employee?->id,
                'acquisition_source' => 'WEBSITE',
                'current_balance' => 0.00,
                'credit_limit' => 2000.00,
            ]
        );

        $c1->tags()->syncWithoutDetaching([$tagNew->id]);

        CustomerContact::firstOrCreate(
            ['customer_id' => $c1->id, 'mobile' => '+63 917 123 4567'],
            [
                'name' => 'Maria Dela Cruz',
                'relationship' => 'PRIMARY',
                'email' => 'maria.delacruz@demo.local',
                'is_primary' => true,
                'authorization_level' => 'FULL',
            ]
        );

        CustomerNote::firstOrCreate(
            ['customer_id' => $c1->id, 'note' => 'Customer requested morning installation scheduling.'],
            ['note_type' => 'GENERAL', 'visibility' => 'INTERNAL']
        );

        CustomerActivity::firstOrCreate(
            ['customer_id' => $c1->id, 'activity_type' => 'CUSTOMER_CREATED'],
            [
                'title' => 'Customer Registered [DEMO]',
                'description' => 'Maria Dela Cruz created as ACTIVE subscriber.',
                'recorded_at' => now(),
            ]
        );

        // Business Customer
        $c2 = Customer::firstOrCreate(
            ['customer_number' => 'CUST-000002'],
            [
                'account_number' => 'ACC-000002',
                'business_name' => 'Manila IT Solutions Corp. [DEMO]',
                'trade_name' => 'Manila IT Solutions',
                'legal_name' => 'Manila IT Solutions Corporation',
                'contact_person' => 'Juan Tamad',
                'customer_type' => 'BUSINESS',
                'status' => 'ACTIVE',
                'primary_phone' => '+63 2 8555 9900',
                'email' => 'contact@manilait.demo',
                'branch_id' => $branch?->id,
                'assigned_employee_id' => $employee?->id,
                'acquisition_source' => 'FIELD_SALES',
                'current_balance' => 0.00,
                'credit_limit' => 10000.00,
            ]
        );

        $c2->tags()->syncWithoutDetaching([$tagVip->id, $tagBiz->id]);

        // Referral
        $c2->update(['referred_by_customer_id' => $c1->id]);

        // CRM Leads
        $lead = Lead::firstOrCreate(
            ['lead_number' => 'LEAD-000001'],
            [
                'name' => 'Pedro Penduko',
                'company' => 'Penduko Enterprise',
                'mobile' => '+63 920 987 6543',
                'email' => 'pedro@penduko.demo',
                'source' => 'FACEBOOK',
                'branch_id' => $branch?->id,
                'assigned_employee_id' => $employee?->id,
                'interested_package_id' => $package?->id,
                'priority' => 'HIGH',
                'status' => 'QUALIFIED',
                'expected_conversion_date' => now()->addDays(5)->toDateString(),
                'notes' => 'Inquired about 100Mbps Business Fiber Plan.',
            ]
        );

        LeadActivity::firstOrCreate(
            ['lead_id' => $lead->id, 'outcome' => 'Interested in proposal'],
            [
                'employee_id' => $employee?->id,
                'activity_type' => 'PHONE_CALL',
                'scheduled_at' => now()->subDay(),
                'completed_at' => now()->subDay(),
                'notes' => 'Called lead to present summer package discounts.',
                'status' => 'COMPLETED',
            ]
        );
    }
}
