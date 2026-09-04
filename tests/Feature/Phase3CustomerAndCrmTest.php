<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\CustomerStatusHistory;
use App\Models\Lead;
use App\Services\CustomerService;
use App\Services\CustomerStatusService;
use App\Services\CustomerDuplicateDetectionService;
use App\Services\CustomerDocumentService;
use App\Services\LeadConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase3CustomerAndCrmTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->adminUser = User::factory()->create(['status' => 'ACTIVE']);
        $adminRole = Role::where('code', 'SUPER_ADMIN')->first();
        if ($adminRole) {
            $this->adminUser->roles()->attach($adminRole->id);
        }
    }

    public function test_customer_creation_generates_sequential_numbers()
    {
        $service = app(CustomerService::class);

        $customer = $service->createCustomer([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'customer_type' => 'RESIDENTIAL',
            'primary_phone' => '+63 917 111 2233',
            'email' => 'juan.delacruz@demo.local',
            'acquisition_source' => 'WEBSITE',
        ]);

        $this->assertNotEmpty($customer->customer_number);
        $this->assertNotEmpty($customer->account_number);
        $this->assertStringStartsWith('CUST-', $customer->customer_number);
        $this->assertStringStartsWith('ACC-', $customer->account_number);
        $this->assertEquals('PROSPECT', $customer->status);
    }

    public function test_atomic_customer_status_transition_creates_immutable_history()
    {
        $customer = Customer::first();
        $statusService = app(CustomerStatusService::class);

        $updated = $statusService->transition(
            $customer,
            'ACTIVE',
            'Identity verified and initial deposit received',
            'Verified by CSR'
        );

        $this->assertEquals('ACTIVE', $updated->status);
        $this->assertDatabaseHas('customer_status_histories', [
            'customer_id' => $customer->id,
            'new_status' => 'ACTIVE',
            'reason' => 'Identity verified and initial deposit received',
        ]);
        $this->assertDatabaseHas('customer_activities', [
            'customer_id' => $customer->id,
            'activity_type' => 'STATUS_CHANGED',
        ]);
    }

    public function test_customer_duplicate_detection_flags_matching_records()
    {
        $dupService = app(CustomerDuplicateDetectionService::class);

        $existing = Customer::first();

        $matches = $dupService->findDuplicates([
            'primary_phone' => $existing->primary_phone,
            'email' => $existing->email,
        ]);

        $this->assertTrue($matches->isNotEmpty());
        $this->assertEquals($existing->id, $matches->first()->id);
    }

    public function test_private_document_upload_and_authorized_download()
    {
        Storage::fake();

        $customer = Customer::first();
        $docService = app(CustomerDocumentService::class);

        $file = UploadedFile::fake()->create('valid_id.pdf', 500, 'application/pdf');

        $doc = $docService->storeDocument(
            $customer->id,
            $file,
            'VALID_ID',
            'ID-998877'
        );

        $this->assertDatabaseHas('customer_documents', [
            'customer_id' => $customer->id,
            'document_type' => 'VALID_ID',
            'original_filename' => 'valid_id.pdf',
        ]);

        Storage::assertExists($doc->storage_path);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.customers.documents.download', $doc));

        $response->assertStatus(200);
    }

    public function test_lead_creation_and_atomic_conversion_to_customer()
    {
        $lead = Lead::factory()->create([
            'status' => 'QUALIFIED',
            'mobile' => '+63 920 123 9988',
            'email' => 'lead.test@demo.local',
        ]);

        $conversionService = app(LeadConversionService::class);

        $customer = $conversionService->convertToCustomer($lead);

        $this->assertEquals('CONVERTED', $lead->fresh()->status);
        $this->assertEquals($customer->id, $lead->fresh()->converted_customer_id);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'primary_phone' => '+63 920 123 9988',
        ]);
    }

    public function test_crm_dashboard_and_customer_360_profile_view()
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.crm.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('CRM & Customer Dashboard');

        $customer = Customer::first();
        $response360 = $this->actingAs($this->adminUser)->get(route('admin.customers.show', $customer));
        $response360->assertStatus(200);
        $response360->assertSee($customer->customer_number);
    }

    public function test_migration_rollback_and_reapply_cleanly()
    {
        $this->artisan('migrate:rollback')
            ->assertExitCode(0);

        $this->artisan('migrate')
            ->assertExitCode(0);
    }
}
