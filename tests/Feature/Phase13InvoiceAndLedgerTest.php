<?php

namespace Tests\Feature;

use App\Models\BillableCharge;
use App\Models\BillingProfile;
use App\Models\Customer;
use App\Models\InstallationHandoff;
use App\Models\Invoice;
use App\Models\LedgerTransaction;
use App\Models\ServiceAccount;
use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Models\Subscription;
use App\Models\Tax;
use App\Models\User;
use App\Services\BalanceService;
use App\Services\BillingRunService;
use App\Services\FinancialReconciliationService;
use App\Services\FinancialReversalService;
use App\Services\InvoiceCancellationService;
use App\Services\InvoiceFinalizationService;
use App\Services\InvoiceGenerationService;
use App\Services\ServiceActivationService;
use App\Services\StatementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase13InvoiceAndLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Customer $customer;
    protected ServiceAccount $serviceAccount;
    protected Subscription $subscription;
    protected BillableCharge $charge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders::class);

        $this->adminUser = User::where('email', 'admin@isp.test')->first() ?? User::factory()->create();
        $this->customer = Customer::first();

        $package = ServicePackage::first();
        $version = ServicePackageVersion::where('package_id', $package->id)->latest()->first();

        $handoff = InstallationHandoff::create([
            'customer_id' => $this->customer->id,
            'package_id' => $package->id,
            'package_version_id' => $version->id,
            'status' => 'READY_FOR_ACTIVATION',
        ]);

        $activationService = app(ServiceActivationService::class);
        $this->subscription = $activationService->activateFromInstallationHandoff($handoff, $this->adminUser->id);
        $this->serviceAccount = $this->subscription->serviceAccount;

        $vat = Tax::where('code', 'VAT12')->first();

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
                'tax_id' => $vat?->id,
                'currency' => 'PHP',
                'status' => 'ACTIVE',
            ]
        );

        $runService = app(BillingRunService::class);
        $runService->createAndExecuteRun(now()->startOfMonth()->toDateString(), 'MONTHLY', $this->adminUser->id);

        $this->charge = BillableCharge::where('service_account_id', $this->serviceAccount->id)->firstOrFail();
    }

    public function test_generates_invoice_from_billable_charges_and_maps_line_items(): void
    {
        $genService = app(InvoiceGenerationService::class);

        $invoice = $genService->generateForServiceAccount($this->serviceAccount, null, $this->adminUser->id);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'customer_id' => $this->customer->id,
            'service_account_id' => $this->serviceAccount->id,
            'status' => 'DRAFT',
            'total_amount' => $this->charge->total_amount,
        ]);

        $this->assertDatabaseHas('invoice_lines', [
            'invoice_id' => $invoice->id,
            'charge_id' => $this->charge->id,
            'total_amount' => $this->charge->total_amount,
        ]);

        $this->assertEquals('INVOICED', $this->charge->fresh()->status);
    }

    public function test_finalizes_invoice_and_posts_debit_to_authoritative_ledger(): void
    {
        $genService = app(InvoiceGenerationService::class);
        $invoice = $genService->generateForServiceAccount($this->serviceAccount, null, $this->adminUser->id);

        $finService = app(InvoiceFinalizationService::class);
        $finService->finalizeInvoice($invoice, $this->adminUser->id);

        $this->assertEquals('FINALIZED', $invoice->fresh()->status);

        $this->assertDatabaseHas('ledger_transactions', [
            'customer_id' => $this->customer->id,
            'service_account_id' => $this->serviceAccount->id,
            'invoice_id' => $invoice->id,
            'transaction_type' => 'INVOICE',
            'debit_amount' => $invoice->total_amount,
            'status' => 'POSTED',
        ]);

        $balanceService = app(BalanceService::class);
        $balance = $balanceService->getCustomerBalance($this->customer->id);

        $this->assertEquals((float) $invoice->total_amount, $balance);
    }

    public function test_reverses_ledger_transaction_on_invoice_cancellation(): void
    {
        $genService = app(InvoiceGenerationService::class);
        $invoice = $genService->generateForServiceAccount($this->serviceAccount, null, $this->adminUser->id);

        $finService = app(InvoiceFinalizationService::class);
        $finService->finalizeInvoice($invoice, $this->adminUser->id);

        $cancelService = app(InvoiceCancellationService::class);
        $cancelService->cancelInvoice($invoice, 'Billing calculation error', $this->adminUser->id);

        $this->assertEquals('CANCELLED', $invoice->fresh()->status);

        $this->assertDatabaseHas('ledger_transactions', [
            'invoice_id' => $invoice->id,
            'transaction_type' => 'INVOICE',
            'status' => 'REVERSED',
        ]);

        $this->assertDatabaseHas('ledger_transactions', [
            'invoice_id' => $invoice->id,
            'transaction_type' => 'REVERSAL',
            'credit_amount' => $invoice->total_amount,
            'status' => 'POSTED',
        ]);

        $balanceService = app(BalanceService::class);
        $balance = $balanceService->getCustomerBalance($this->customer->id);

        $this->assertEquals(0.00, $balance);
    }

    public function test_account_statement_generation(): void
    {
        $genService = app(InvoiceGenerationService::class);
        $invoice = $genService->generateForServiceAccount($this->serviceAccount, null, $this->adminUser->id);

        $finService = app(InvoiceFinalizationService::class);
        $finService->finalizeInvoice($invoice, $this->adminUser->id);

        $stmtService = app(StatementService::class);
        $statement = $stmtService->generateStatement($this->customer, $this->serviceAccount);

        $this->assertEquals($this->customer->customer_number, $statement['customer_number']);
        $this->assertEquals((float) $invoice->total_amount, $statement['closing_balance']);
        $this->assertCount(1, $statement['lines']);
    }

    public function test_reconciliation_detects_no_discrepancies_when_properly_invoiced(): void
    {
        $genService = app(InvoiceGenerationService::class);
        $invoice = $genService->generateForServiceAccount($this->serviceAccount, null, $this->adminUser->id);

        $finService = app(InvoiceFinalizationService::class);
        $finService->finalizeInvoice($invoice, $this->adminUser->id);

        $reconService = app(FinancialReconciliationService::class);
        $recon = $reconService->reconcileBillingToInvoices();

        $this->assertTrue($recon['reconciled']);
        $this->assertEquals(0, $recon['mismatch_count']);
    }
}
