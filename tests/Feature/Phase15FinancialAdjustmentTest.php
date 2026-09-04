<?php

namespace Tests\Feature;

use App\Models\Credit;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\FinancialAdjustment;
use App\Models\Invoice;
use App\Models\LedgerTransaction;
use App\Models\Payment;
use App\Models\Rebate;
use App\Models\RefundRequest;
use App\Models\ServiceAccount;
use App\Models\User;
use App\Services\CreditService;
use App\Services\DiscountService;
use App\Services\FinancialAdjustmentService;
use App\Services\RebateService;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase15FinancialAdjustmentTest extends TestCase
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
            'invoice_number' => 'INV-TEST-15001',
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

    public function test_discount_calculation_and_cap_enforcement(): void
    {
        $discountService = app(DiscountService::class);

        $discount = $discountService->createDiscount([
            'name' => 'Test Promo 20%',
            'discount_type' => 'PERCENTAGE',
            'value' => 20.00,
            'max_discount_amount' => 150.00, // Cap at 150
            'min_invoice_amount' => 500.00,
            'is_active' => true,
        ]);

        $result = $discountService->calculateEligibleDiscounts($this->customer, 1000.00);

        $this->assertEquals(150.00, $result['total_discount']);
        $this->assertCount(1, $result['items']);
    }

    public function test_credit_issuance_and_invoice_application(): void
    {
        $creditService = app(CreditService::class);

        $credit = $creditService->issueCredit(
            customer: $this->customer,
            amount: 500.00,
            creditType: 'GOODWILL',
            reason: 'Downtime compensation',
            userId: $this->user->id
        );

        $this->assertEquals('AVAILABLE', $credit->status);
        $this->assertEquals(500.00, (float)$credit->remaining_amount);

        // Apply credit to invoice
        $app = $creditService->applyCreditToInvoice($credit, $this->invoice, 500.00, $this->user->id);

        $credit->refresh();
        $this->invoice->refresh();

        $this->assertEquals('FULLY_USED', $credit->status);
        $this->assertEquals(0.00, (float)$credit->remaining_amount);
        $this->assertEquals('PARTIALLY_PAID', $this->invoice->status);
        $this->assertEquals(500.00, (float)$this->invoice->paid_amount);

        // Ledger verification
        $ledgerTx = LedgerTransaction::where('invoice_id', $this->invoice->id)
            ->where('transaction_type', 'CREDIT')
            ->first();
        $this->assertNotNull($ledgerTx);
        $this->assertEquals(500.00, (float)$ledgerTx->amount);
    }

    public function test_referral_rebate_issuance(): void
    {
        $rebateService = app(RebateService::class);

        $referred = Customer::create([
            'customer_number' => 'CUST-REF-999',
            'account_number' => 'ACC-REF-999',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'primary_phone' => '09123456789',
            'customer_type' => 'RESIDENTIAL',
            'status' => 'ACTIVE',
        ]);

        $rebate = $rebateService->processReferralRebate($this->customer, $referred, 500.00, $this->user->id);

        $this->assertEquals('ISSUED', $rebate->status);
        $this->assertEquals(500.00, (float)$rebate->amount);

        // Verify credit generated for referring customer
        $credit = Credit::find($rebate->credit_id);
        $this->assertNotNull($credit);
        $this->assertEquals(500.00, (float)$credit->total_amount);
    }

    public function test_refund_request_approval_and_ledger_posting(): void
    {
        $refundService = app(RefundService::class);
        $paymentService = app(\App\Services\PaymentService::class);

        $payment = $paymentService->recordPayment(
            customer: $this->customer,
            amount: 1120.00,
            paymentMethodCode: 'CASH',
            userId: $this->user->id
        );

        $request = $refundService->createRefundRequest(
            payment: $payment,
            amount: 500.00,
            reason: 'Customer overpayment',
            refundType: 'CASH',
            userId: $this->user->id
        );

        $this->assertEquals('REQUESTED', $request->approval_status);

        // Approve and process
        $refundService->approveRefundRequest($request, $this->user->id);
        $tx = $refundService->processRefund($request, 'VOUCHER-REF-101', $this->user->id);

        $request->refresh();
        $this->assertEquals('PROCESSED', $request->processing_status);

        // Ledger entry
        $ledgerTx = LedgerTransaction::where('payment_id', $payment->id)
            ->where('transaction_type', 'REFUND')
            ->first();

        $this->assertNotNull($ledgerTx);
        $this->assertEquals(500.00, (float)$ledgerTx->amount);
    }

    public function test_financial_adjustment_approval_threshold(): void
    {
        $adjService = app(FinancialAdjustmentService::class);

        // Threshold = 500.00
        $adj = $adjService->createAdjustment(
            customer: $this->customer,
            amount: 1000.00,
            adjustmentType: 'CREDIT_ADJUSTMENT',
            reason: 'High value dispute adjustment',
            userId: $this->user->id,
            approvalThreshold: 500.00
        );

        $this->assertEquals('PENDING_APPROVAL', $adj->status);

        // Approve adjustment
        $adjService->approveAdjustment($adj, $this->user->id);

        $adj->refresh();
        $this->assertEquals('POSTED', $adj->status);
        $this->assertNotNull($adj->ledger_transaction_id);
    }
}
