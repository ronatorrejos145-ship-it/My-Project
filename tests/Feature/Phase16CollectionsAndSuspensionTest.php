<?php

namespace Tests\Feature;

use App\Models\CollectionAccount;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentArrangement;
use App\Models\PromiseToPay;
use App\Models\ReconnectionRequest;
use App\Models\ServiceAccount;
use App\Models\Subscription;
use App\Models\SuspensionRequest;
use App\Models\User;
use App\Models\WriteOffRequest;
use App\Services\CollectionActionService;
use App\Services\DelinquencyEngineService;
use App\Services\PaymentAllocationService;
use App\Services\PaymentService;
use App\Services\ReconnectionService;
use App\Services\SuspensionService;
use App\Services\WriteOffService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase16CollectionsAndSuspensionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected ServiceAccount $serviceAccount;
    protected Subscription $subscription;
    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->user = User::first();
        $this->actingAs($this->user);

        $this->customer = Customer::where('status', 'ACTIVE')->first();
        $branch = \App\Models\Branch::first();
        $this->serviceAccount = ServiceAccount::where('customer_id', $this->customer->id)->first()
            ?? ServiceAccount::create([
                'account_number' => 'ACCT-TEST-16001',
                'customer_id' => $this->customer->id,
                'branch_id' => $branch?->id ?? 1,
                'service_address' => '123 Collections St',
                'status' => 'ACTIVE',
            ]);

        $this->subscription = Subscription::where('customer_id', $this->customer->id)->first()
            ?? Subscription::create([
                'subscription_number' => 'SUB-TEST-16001',
                'customer_id' => $this->customer->id,
                'service_account_id' => $this->serviceAccount->id,
                'package_id' => 1,
                'package_version_id' => 1,
                'monthly_rate' => 1500.00,
                'status' => 'ACTIVE',
            ]);

        $this->invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-16001',
            'customer_id' => $this->customer->id,
            'service_account_id' => $this->serviceAccount->id,
            'invoice_date' => now()->subDays(20)->toDateString(),
            'billing_period_start' => now()->subDays(50)->toDateString(),
            'billing_period_end' => now()->subDays(20)->toDateString(),
            'subtotal' => 1500.00,
            'tax_amount' => 0.00,
            'total_amount' => 1500.00,
            'paid_amount' => 0.00,
            'due_date' => now()->subDays(16)->toDateString(), // 16 days overdue
            'status' => 'OVERDUE',
            'is_finalized' => true,
        ]);
    }

    public function test_delinquency_evaluation_and_aging_calculation(): void
    {
        $delinquencyService = app(DelinquencyEngineService::class);

        $account = $delinquencyService->evaluateCustomerDelinquency($this->customer);

        $this->assertEquals('SUSPENSION_ELIGIBLE', $account->delinquency_status);
        $this->assertEquals(1500.00, (float)$account->overdue_amount);
        $this->assertTrue($account->days_overdue >= 15);

        $aging = $delinquencyService->calculateArAgingBuckets();
        $this->assertGreaterThan(0, $aging['16_30_DAYS']);
    }

    public function test_promise_to_pay_and_payment_arrangement_creation(): void
    {
        $actionService = app(CollectionActionService::class);

        // Promise to Pay
        $promise = $actionService->createPromiseToPay(
            customer: $this->customer,
            amount: 1500.00,
            promisedDate: now()->addDays(5)->toDateString(),
            notes: 'Promise next week',
            userId: $this->user->id
        );

        $this->assertEquals('ACTIVE', $promise->status);
        $this->assertEquals(1500.00, (float)$promise->promised_amount);

        // Payment Arrangement
        $arrangement = $actionService->createPaymentArrangement(
            customer: $this->customer,
            totalAmount: 1500.00,
            downPayment: 300.00,
            installmentsCount: 3,
            startDate: now()->toDateString(),
            userId: $this->user->id
        );

        $this->assertEquals('PENDING_APPROVAL', $arrangement->status);
        $this->assertCount(3, $arrangement->installments);

        $actionService->approvePaymentArrangement($arrangement, $this->user->id);
        $arrangement->refresh();
        $this->assertEquals('ACTIVE', $arrangement->status);
    }

    public function test_suspension_execution_and_commercial_status_update(): void
    {
        $suspensionService = app(SuspensionService::class);

        $this->assertTrue($suspensionService->isEligibleForSuspension($this->customer));

        $request = $suspensionService->requestSuspension(
            customer: $this->customer,
            reason: 'Unpaid 16-day overdue invoice',
            subscription: $this->subscription,
            requestedBy: $this->user->id
        );

        $this->assertEquals('APPROVED', $request->approval_status);
        $this->assertEquals('COMPLETED', $request->network_action_status);

        $this->subscription->refresh();
        $this->assertEquals('SUSPENDED', $this->subscription->status);

        $collAccount = CollectionAccount::where('customer_id', $this->customer->id)->first();
        $this->assertEquals('SUSPENDED', $collAccount->delinquency_status);
    }

    public function test_payment_and_reconnection_workflow(): void
    {
        $suspensionService = app(SuspensionService::class);
        $reconnectionService = app(ReconnectionService::class);
        $paymentService = app(PaymentService::class);
        $allocationService = app(PaymentAllocationService::class);

        // Suspend first
        $suspensionService->requestSuspension($this->customer, 'Overdue', $this->subscription, $this->user->id);

        // Record payment & allocate
        $payment = $paymentService->recordPayment(
            customer: $this->customer,
            amount: 1500.00,
            paymentMethodCode: 'CASH',
            userId: $this->user->id
        );
        $allocationService->allocatePayment($payment, $this->invoice, $this->user->id);

        // Request reconnection
        $recon = $reconnectionService->requestReconnection(
            customer: $this->customer,
            payment: $payment,
            reconnectionFee: 100.00,
            feeWaived: true,
            waivedBy: $this->user->id,
            requestedBy: $this->user->id
        );

        $this->assertEquals('APPROVED', $recon->approval_status);
        $this->assertEquals('COMPLETED', $recon->network_action_status);

        $this->subscription->refresh();
        $this->assertEquals('ACTIVE', $this->subscription->status);

        $collAccount = CollectionAccount::where('customer_id', $this->customer->id)->first();
        $this->assertEquals('CURRENT', $collAccount->delinquency_status);
        $this->assertEquals(0.00, (float)$collAccount->overdue_amount);
    }

    public function test_bad_debt_write_off_approval_and_ledger_posting(): void
    {
        $writeOffService = app(WriteOffService::class);

        $request = $writeOffService->createWriteOffRequest(
            customer: $this->customer,
            amount: 1500.00,
            reason: 'Uncollectible customer moved out of town',
            invoice: $this->invoice,
            userId: $this->user->id
        );

        $this->assertEquals('PENDING_APPROVAL', $request->status);

        $writeOffService->approveAndPostWriteOff($request, $this->user->id);

        $request->refresh();
        $this->assertEquals('POSTED', $request->status);
        $this->assertNotNull($request->ledger_transaction_id);

        $collAccount = CollectionAccount::where('customer_id', $this->customer->id)->first();
        $this->assertEquals('WRITTEN_OFF', $collAccount->delinquency_status);
    }
}
