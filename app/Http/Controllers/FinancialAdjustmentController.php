<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\Credit;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\FinancialAdjustment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Rebate;
use App\Models\RefundRequest;
use App\Services\CreditService;
use App\Services\DiscountService;
use App\Services\FinancialAdjustmentService;
use App\Services\RebateService;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialAdjustmentController extends Controller
{
    public function __construct(
        protected DiscountService $discountService,
        protected CreditService $creditService,
        protected RebateService $rebateService,
        protected RefundService $refundService,
        protected FinancialAdjustmentService $adjustmentService
    ) {}

    // 1. Discounts & Campaigns Management
    public function discounts(Request $request)
    {
        $discounts = Discount::with(['applicablePackage', 'applicableCustomer'])
            ->orderBy('priority', 'desc')
            ->paginate(20);

        return view('admin.finance.adjustments.discounts', compact('discounts'));
    }

    public function storeDiscount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:PERCENTAGE,FIXED_AMOUNT',
            'value' => 'required|numeric|min:0.01',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_invoice_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'stacking_allowed' => 'nullable|boolean',
            'priority' => 'required|integer|min:1',
        ]);

        $validated['stacking_allowed'] = $request->has('stacking_allowed');
        $discount = $this->discountService->createDiscount($validated);

        return redirect()->back()->with('success', "Discount {$discount->code} created successfully.");
    }

    // 2. Account Credits Management
    public function credits(Request $request)
    {
        $credits = Credit::with(['customer', 'serviceAccount', 'createdBy', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $customers = Customer::where('status', 'ACTIVE')->limit(100)->get();

        return view('admin.finance.adjustments.credits', compact('credits', 'customers'));
    }

    public function issueCredit(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'credit_type' => 'required|string',
            'reason' => 'required|string|max:500',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        $credit = $this->creditService->issueCredit(
            customer: $customer,
            amount: (float)$validated['amount'],
            creditType: $validated['credit_type'],
            reason: $validated['reason'],
            userId: Auth::id()
        );

        return redirect()->back()->with('success', "Credit {$credit->credit_number} issued to {$customer->full_name}.");
    }

    public function applyCredit(Request $request, Credit $credit)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'nullable|numeric|min:0.01',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        $this->creditService->applyCreditToInvoice(
            credit: $credit,
            invoice: $invoice,
            amount: !empty($validated['amount']) ? (float)$validated['amount'] : null,
            userId: Auth::id()
        );

        return redirect()->back()->with('success', "Credit {$credit->credit_number} applied to Invoice {$invoice->invoice_number}.");
    }

    // 3. Rebates Management
    public function rebates(Request $request)
    {
        $rebates = Rebate::with(['customer', 'referredCustomer', 'credit'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $customers = Customer::where('status', 'ACTIVE')->limit(100)->get();

        return view('admin.finance.adjustments.rebates', compact('rebates', 'customers'));
    }

    public function storeReferralRebate(Request $request)
    {
        $validated = $request->validate([
            'referring_customer_id' => 'required|exists:customers,id',
            'referred_customer_id' => 'required|exists:customers,id|different:referring_customer_id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $referring = Customer::findOrFail($validated['referring_customer_id']);
        $referred = Customer::findOrFail($validated['referred_customer_id']);

        $rebate = $this->rebateService->processReferralRebate(
            referringCustomer: $referring,
            referredCustomer: $referred,
            amount: (float)$validated['amount'],
            userId: Auth::id()
        );

        return redirect()->back()->with('success', "Referral Rebate {$rebate->rebate_number} issued successfully.");
    }

    // 4. Refunds Management
    public function refunds(Request $request)
    {
        $refunds = RefundRequest::with(['customer', 'payment', 'invoice', 'transactions', 'reversals'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $payments = Payment::where('status', 'VERIFIED')->limit(100)->get();

        return view('admin.finance.adjustments.refunds', compact('refunds', 'payments'));
    }

    public function storeRefund(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'requested_amount' => 'required|numeric|min:0.01',
            'refund_type' => 'required|string',
            'reason' => 'required|string|max:500',
        ]);

        $payment = Payment::findOrFail($validated['payment_id']);

        $refund = $this->refundService->createRefundRequest(
            payment: $payment,
            amount: (float)$validated['requested_amount'],
            reason: $validated['reason'],
            refundType: $validated['refund_type'],
            userId: Auth::id()
        );

        return redirect()->back()->with('success', "Refund Request {$refund->refund_number} queued.");
    }

    public function approveRefund(RefundRequest $refund)
    {
        $this->refundService->approveRefundRequest($refund, Auth::id());
        return redirect()->back()->with('success', "Refund {$refund->refund_number} approved.");
    }

    public function processRefund(Request $request, RefundRequest $refund)
    {
        $validated = $request->validate([
            'transaction_reference' => 'required|string|max:100',
        ]);

        $this->refundService->processRefund($refund, $validated['transaction_reference'], Auth::id());
        return redirect()->back()->with('success', "Refund {$refund->refund_number} processed and posted to ledger.");
    }

    public function reverseRefund(Request $request, RefundRequest $refund)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $this->refundService->reverseRefund($refund, $validated['reason'], Auth::id());
        return redirect()->back()->with('success', "Refund {$refund->refund_number} reversed.");
    }

    // 5. Manual Financial Adjustments & Approvals
    public function adjustments(Request $request)
    {
        $adjustments = FinancialAdjustment::with(['customer', 'createdBy', 'approvedBy', 'ledgerTransaction'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $pendingApprovals = ApprovalRequest::where('status', 'PENDING')
            ->with(['requestedBy'])
            ->get();

        $customers = Customer::where('status', 'ACTIVE')->limit(100)->get();

        return view('admin.finance.adjustments.index', compact('adjustments', 'pendingApprovals', 'customers'));
    }

    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'adjustment_type' => 'required|in:DEBIT_ADJUSTMENT,CREDIT_ADJUSTMENT,WRITE_OFF,CORRECTION',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        $adj = $this->adjustmentService->createAdjustment(
            customer: $customer,
            amount: (float)$validated['amount'],
            adjustmentType: $validated['adjustment_type'],
            reason: $validated['reason'],
            notes: $validated['notes'] ?? null,
            userId: Auth::id()
        );

        $msg = ($adj->status === 'PENDING_APPROVAL')
            ? "Adjustment {$adj->adjustment_number} created and submitted for supervisor approval."
            : "Adjustment {$adj->adjustment_number} posted successfully.";

        return redirect()->back()->with('success', $msg);
    }

    public function approveAdjustment(FinancialAdjustment $adjustment)
    {
        $this->adjustmentService->approveAdjustment($adjustment, Auth::id());
        return redirect()->back()->with('success', "Adjustment {$adjustment->adjustment_number} approved and posted to ledger.");
    }
}
