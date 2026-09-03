<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerServiceRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServiceAccount;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase17CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $customerUser;
    protected Customer $customer;
    protected User $otherCustomerUser;
    protected Customer $otherCustomer;
    protected Invoice $customerInvoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->customerUser = User::first();
        $this->customer = Customer::where('status', 'ACTIVE')->first();
        $this->customer->update(['user_id' => $this->customerUser->id]);

        $this->otherCustomerUser = User::factory()->create();
        $this->otherCustomer = Customer::create([
            'customer_number' => 'CUST-OTHER-99',
            'account_number' => 'ACC-OTHER-99',
            'user_id' => $this->otherCustomerUser->id,
            'first_name' => 'Other',
            'last_name' => 'User',
            'primary_phone' => '09998887766',
            'customer_type' => 'RESIDENTIAL',
            'status' => 'ACTIVE',
        ]);

        $branch = \App\Models\Branch::first();
        $sa = ServiceAccount::create([
            'account_number' => 'ACCT-PORTAL-01',
            'customer_id' => $this->customer->id,
            'branch_id' => $branch?->id ?? 1,
            'service_address' => '456 Portal Way',
            'status' => 'ACTIVE',
        ]);

        $this->customerInvoice = Invoice::create([
            'invoice_number' => 'INV-PORTAL-01',
            'customer_id' => $this->customer->id,
            'service_account_id' => $sa->id,
            'invoice_date' => now()->toDateString(),
            'billing_period_start' => now()->startOfMonth()->toDateString(),
            'billing_period_end' => now()->endOfMonth()->toDateString(),
            'subtotal' => 1200.00,
            'tax_amount' => 0.00,
            'total_amount' => 1200.00,
            'paid_amount' => 0.00,
            'due_date' => now()->addDays(10)->toDateString(),
            'status' => 'UNPAID',
            'is_finalized' => true,
        ]);
    }

    public function test_customer_can_access_own_dashboard(): void
    {
        $response = $this->actingAs($this->customerUser)->get(route('portal.dashboard'));

        $response->assertStatus(200);
        $response->assertSee($this->customer->customer_number);
    }

    public function test_customer_can_view_own_invoices(): void
    {
        $response = $this->actingAs($this->customerUser)->get(route('portal.invoices'));

        $response->assertStatus(200);
        $response->assertSee('INV-PORTAL-01');
    }

    public function test_customer_cannot_view_other_customers_invoice_idor_protection(): void
    {
        // Other customer tries to view $this->customerInvoice
        $response = $this->actingAs($this->otherCustomerUser)->get(route('portal.invoices.show', $this->customerInvoice));

        $response->assertStatus(403);
    }

    public function test_customer_can_submit_self_service_request(): void
    {
        $response = $this->actingAs($this->customerUser)->post(route('portal.requests.store'), [
            'request_type' => 'UPGRADE',
            'notes' => 'Requesting 100 Mbps upgrade via portal',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('customer_service_requests', [
            'customer_id' => $this->customer->id,
            'request_type' => 'UPGRADE',
            'status' => 'SUBMITTED',
        ]);
    }

    public function test_customer_api_scoped_responses(): void
    {
        $response = $this->actingAs($this->customerUser)->getJson(route('api.customer.invoices'));

        $response->assertStatus(200);
        $response->assertJsonFragment(['invoice_number' => 'INV-PORTAL-01']);
    }
}
