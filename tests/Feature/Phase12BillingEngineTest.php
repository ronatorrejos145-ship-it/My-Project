<?php

namespace Tests\Feature;

use App\Models\BillableCharge;
use App\Models\BillingProfile;
use App\Models\BillingRun;
use App\Models\Customer;
use App\Models\InstallationHandoff;
use App\Models\ServiceAccount;
use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Models\Subscription;
use App\Models\Tax;
use App\Models\User;
use App\Services\BillingPreviewService;
use App\Services\BillingRunService;
use App\Services\ChargeGenerationService;
use App\Services\ProrationCalculator;
use App\Services\ServiceActivationService;
use App\Services\TaxCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase12BillingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Customer $customer;
    protected ServiceAccount $serviceAccount;
    protected Subscription $subscription;
    protected ServicePackage $package;
    protected ServicePackageVersion $version;
    protected Tax $tax;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders::class);

        $this->adminUser = User::where('email', 'admin@isp.test')->first() ?? User::factory()->create();
        $this->customer = Customer::first();
        $this->package = ServicePackage::first();
        $this->version = ServicePackageVersion::where('package_id', $this->package->id)->latest()->first();

        $this->tax = Tax::where('code', 'VAT12')->first() ?? Tax::create(['code' => 'VAT12', 'name' => '12% VAT', 'rate' => 12.00, 'is_inclusive' => false, 'status' => 'ACTIVE']);

        $handoff = InstallationHandoff::create([
            'customer_id' => $this->customer->id,
            'package_id' => $this->package->id,
            'package_version_id' => $this->version->id,
            'status' => 'READY_FOR_ACTIVATION',
        ]);

        $activationService = app(ServiceActivationService::class);
        $this->subscription = $activationService->activateFromInstallationHandoff($handoff, $this->adminUser->id);
        $this->serviceAccount = $this->subscription->serviceAccount;

        // Ensure billing profile exists
        BillingProfile::firstOrCreate(
            ['service_account_id' => $this->serviceAccount->id],
            [
                'billing_method' => 'POSTPAID',
                'billing_cycle' => 'MONTHLY',
                'billing_day' => 1,
                'billing_start_date' => now()->startOfMonth()->toDateString(),
                'next_billing_date' => now()->startOfMonth()->toDateString(),
                'due_days' => 15,
                'grace_days' => 3,
                'tax_id' => $this->tax->id,
                'currency' => 'PHP',
                'status' => 'ACTIVE',
                'auto_bill_enabled' => true,
            ]
        );
    }

    public function test_proration_calculator_computes_exact_mid_cycle_prorated_amount(): void
    {
        $calculator = app(ProrationCalculator::class);

        // Full price PHP 1500, service starts on May 16 in a 31-day month (May 1 to May 31). Used days = 16 (May 16..31).
        $result = $calculator->calculateProration(1500.00, '2026-05-16', '2026-05-01', '2026-05-31', 'CALENDAR_DAY');

        $this->assertEquals(31, $result['total_days']);
        $this->assertEquals(16, $result['used_days']);
        $this->assertEquals(774.19, $result['prorated_amount']);
    }

    public function test_tax_calculator_evaluates_exclusive_and_inclusive_taxes(): void
    {
        $taxService = app(TaxCalculationService::class);

        // Exclusive 12% VAT on 1000 => Tax = 120, Total = 1120
        $exclusive = $taxService->calculateTax(1000.00, $this->tax);
        $this->assertEquals(1000.00, $exclusive['subtotal']);
        $this->assertEquals(120.00, $exclusive['tax_amount']);
        $this->assertEquals(1120.00, $exclusive['total_amount']);

        // Inclusive 12% VAT on 1120 => Subtotal = 1000, Tax = 120, Total = 1120
        $incTax = Tax::create(['code' => 'INC12', 'name' => '12% Inclusive VAT', 'rate' => 12.00, 'is_inclusive' => true, 'status' => 'ACTIVE']);
        $inclusive = $taxService->calculateTax(1120.00, $incTax);
        $this->assertEquals(1000.00, $inclusive['subtotal']);
        $this->assertEquals(120.00, $inclusive['tax_amount']);
        $this->assertEquals(1120.00, $inclusive['total_amount']);
    }

    public function test_executes_batch_billing_run_and_generates_idempotent_charges(): void
    {
        $runService = app(BillingRunService::class);

        $run = $runService->createAndExecuteRun(now()->startOfMonth()->toDateString(), 'MONTHLY', $this->adminUser->id);

        $this->assertEquals('COMPLETED', $run->status);
        $this->assertGreaterThan(0, $run->successful_accounts);

        $this->assertDatabaseHas('billable_charges', [
            'billing_run_id' => $run->id,
            'service_account_id' => $this->serviceAccount->id,
            'charge_type' => 'RECURRING',
            'status' => 'CHARGED',
        ]);

        // Executing same billing run again must produce idempotent charges (no duplicate)
        $run2 = $runService->createAndExecuteRun(now()->startOfMonth()->toDateString(), 'MONTHLY', $this->adminUser->id);
        $this->assertEquals(BillableCharge::where('service_account_id', $this->serviceAccount->id)->count(), 1);
    }

    public function test_customer_billing_preview_estimator(): void
    {
        $previewService = app(BillingPreviewService::class);

        $preview = $previewService->generateCustomerPreview($this->serviceAccount, now()->toDateString());

        $this->assertEquals($this->serviceAccount->account_number, $preview['service_account_number']);
        $this->assertArrayHasKey('estimated_total', $preview);
    }
}
