<?php

namespace Tests\Feature;

use App\Models\BankTransaction;
use App\Models\CashierSession;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LedgerTransaction;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\ServiceAccount;
use App\Models\User;
use App\Services\BankReconciliationService;
use App\Services\CashierSessionService;
use App\Services\PaymentAllocationService;
use App\Services\PaymentReversalService;
use App\Services\PaymentService;
use App\Services\PaymentVerificationService;
use App\Services\PaymentWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase14PaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected ServiceAccount $serviceAccount;
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
                'account_number' => 'ACCT-TEST-0001',
                'customer_id' => $this->customer->id,
                'branch_id' => $branch?->id ?? 1,
                'service_address' => '123 Test St',
                'status' => 'ACTIVE',
            ]);

        $this->invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-99001',
            'customer_id' => $this->customer->id,
            'service_account_id' => $this->serviceAccount->id,
            'invoice_date' => now()->toDateString(),
            'billing_period_start' => now()->startOfMonth()->toDateString(),
            'billing_period_end' => now()->endOfMonth()->toDateString(),
            'subtotal' => 1000.00,
            'tax_amount' => 120.00,
            'total_amount' => 1120.00,
            'paid_amount' => 0.00,
            'due_date' => now()->addDays(15)->toDateString(),
            'status' => 'UNPAID',
            'is_finalized' => true,
        ]);
    }

    public function test_record_cash_payment_and_auto_allocate(): void
    {
        $paymentService = app(PaymentService::class);
        $allocationService = app(PaymentAllocationService::class);

        $payment = $paymentService->recordPayment(
            customer: $this->customer,
            amount: 1120.00,
            paymentMethodCode: 'CASH',
            paymentChannel: 'CASHIER',
            referenceNumber: 'CASH-REF-001',
            notes: 'Exact payment OTC',
            userId: $this->user->id
        );

        $this->assertEquals('VERIFIED', $payment->status);
        $this->assertEquals(1120.00, (float)$payment->amount);

        $result = $allocationService->allocatePayment($payment, $this->invoice, $this->user->id);

        $this->invoice->refresh();
        $this->assertEquals('PAID', $this->invoice->status);
        $this->assertEquals(1120.00, (float)$this->invoice->paid_amount);

        $ledgerTx = LedgerTransaction::where('payment_id', $payment->id)->first();
        $this->assertNotNull($ledgerTx);
        $this->assertEquals('PAYMENT', $ledgerTx->transaction_type);
        $this->assertEquals('CREDIT', $ledgerTx->debit_credit);
        $this->assertEquals(1120.00, (float)$ledgerTx->amount);
    }

    public function test_pending_bank_transfer_verification_workflow(): void
    {
        $paymentService = app(PaymentService::class);
        $verificationService = app(PaymentVerificationService::class);

        $payment = $paymentService->recordPayment(
            customer: $this->customer,
            amount: 500.00,
            paymentMethodCode: 'BANK_TRANSFER',
            paymentChannel: 'BANK',
            referenceNumber: 'BANK-TRX-9988',
            notes: 'Bank deposit slip attached',
            userId: $this->user->id
        );

        $this->assertEquals('PENDING', $payment->status);
        $this->assertEquals('PENDING', $payment->verification_status);

        // Verify & Approve Payment
        $verification = $verificationService->verifyPayment(
            payment: $payment,
            status: 'APPROVED',
            verifiedBy: $this->user->id,
            verificationNotes: 'Bank deposit verified in portal'
        );

        $payment->refresh();
        $this->assertEquals('VERIFIED', $payment->status);
        $this->assertEquals('APPROVED', $verification->status);

        // Invoice should be partially paid
        $this->invoice->refresh();
        $this->assertEquals('PARTIALLY_PAID', $this->invoice->status);
        $this->assertEquals(500.00, (float)$this->invoice->paid_amount);
    }

    public function test_payment_reversal_restores_invoice_status_and_posts_ledger(): void
    {
        $paymentService = app(PaymentService::class);
        $allocationService = app(PaymentAllocationService::class);
        $reversalService = app(PaymentReversalService::class);

        $payment = $paymentService->recordPayment(
            customer: $this->customer,
            amount: 1120.00,
            paymentMethodCode: 'CASH',
            paymentChannel: 'CASHIER',
            userId: $this->user->id
        );

        $allocationService->allocatePayment($payment, $this->invoice, $this->user->id);

        $this->invoice->refresh();
        $this->assertEquals('PAID', $this->invoice->status);

        // Reverse Payment
        $reversalService->reversePayment($payment, 'Bounced or mistake', $this->user->id);

        $payment->refresh();
        $this->assertEquals('REVERSED', $payment->status);

        $this->invoice->refresh();
        $this->assertEquals('UNPAID', $this->invoice->status);
        $this->assertEquals(0.00, (float)$this->invoice->paid_amount);

        $reversalTx = LedgerTransaction::where('payment_id', $payment->id)
            ->where('transaction_type', 'REVERSAL')
            ->first();

        $this->assertNotNull($reversalTx);
        $this->assertEquals('DEBIT', $reversalTx->debit_credit);
        $this->assertEquals(1120.00, (float)$reversalTx->amount);
    }

    public function test_cashier_session_opening_closing_and_reconciliation(): void
    {
        $cashierService = app(CashierSessionService::class);
        $paymentService = app(PaymentService::class);

        // Open session
        $session = $cashierService->openSession($this->user->id, 500.00, 'Shift A Float');
        $this->assertEquals('OPEN', $session->status);

        // Record cash payment
        $paymentService->recordPayment(
            customer: $this->customer,
            amount: 300.00,
            paymentMethodCode: 'CASH',
            paymentChannel: 'CASHIER',
            userId: $this->user->id
        );

        // Close session with 800 expected (500 + 300)
        $closedSession = $cashierService->closeSession($session, 800.00, 'Exact count');

        $this->assertEquals('CLOSED', $closedSession->status);
        $this->assertEquals(800.00, (float)$closedSession->expected_cash);
        $this->assertEquals(0.00, (float)$closedSession->discrepancy);
    }

    public function test_bank_reconciliation_matching(): void
    {
        $paymentService = app(PaymentService::class);
        $bankRecon = app(BankReconciliationService::class);

        $payment = $paymentService->recordPayment(
            customer: $this->customer,
            amount: 1120.00,
            paymentMethodCode: 'BANK_TRANSFER',
            paymentChannel: 'BANK',
            referenceNumber: 'BDO-REF-7711',
            userId: $this->user->id
        );

        $bankTx = BankTransaction::create([
            'bank_name' => 'BDO',
            'account_number' => '1234567890',
            'transaction_date' => now()->toDateString(),
            'reference_number' => 'BDO-REF-7711',
            'amount' => 1120.00,
            'description' => 'ONLINE BANKING TRANSFER',
            'status' => 'UNMATCHED',
        ]);

        $matched = $bankRecon->matchTransaction($bankTx, $payment, $this->user->id);

        $this->assertTrue($matched);
        $bankTx->refresh();
        $this->assertEquals('MATCHED', $bankTx->status);
        $payment->refresh();
        $this->assertEquals('VERIFIED', $payment->status);
    }

    public function test_webhook_processing_idempotency(): void
    {
        $webhookService = app(PaymentWebhookService::class);

        $payload = [
            'event_id' => 'evt_test_123456',
            'event_type' => 'payment.paid',
            'data' => [
                'reference_number' => 'GCASH-CHECKOUT-99',
            ]
        ];

        $event1 = $webhookService->processWebhook('gcash', $payload);
        $this->assertEquals('PROCESSED', $event1->status);

        $event2 = $webhookService->processWebhook('gcash', $payload);
        $this->assertEquals($event1->id, $event2->id);
    }
}
