<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\CreditService;
use App\Services\DiscountService;
use App\Services\FinancialAdjustmentService;
use App\Services\RebateService;
use App\Services\RefundService;
use Illuminate\Database\Seeder;

class Phase15FinancialAdjustmentSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::where('status', 'ACTIVE')->first();
        if (!$customer) return;

        $user = User::first();
        $discountService = app(DiscountService::class);
        $creditService = app(CreditService::class);
        $rebateService = app(RebateService::class);
        $refundService = app(RefundService::class);
        $adjustmentService = app(FinancialAdjustmentService::class);

        // 1. Seed Discount Rules
        $discount = $discountService->createDiscount([
            'name' => 'Loyalty Promo 10%',
            'discount_type' => 'PERCENTAGE',
            'value' => 10.00,
            'max_discount_amount' => 500.00,
            'min_invoice_amount' => 500.00,
            'stacking_allowed' => false,
            'priority' => 1,
            'is_active' => true,
        ]);

        // 2. Seed Account Credit
        $credit = $creditService->issueCredit(
            customer: $customer,
            amount: 300.00,
            creditType: 'GOODWILL',
            reason: 'Seeded goodwill gesture account credit',
            userId: $user?->id
        );

        // 3. Seed Financial Adjustment
        $adjustmentService->createAdjustment(
            customer: $customer,
            amount: 150.00,
            adjustmentType: 'CREDIT_ADJUSTMENT',
            reason: 'Seeded billing correction adjustment',
            userId: $user?->id,
            approvalThreshold: 1000.00
        );

        // 4. Seed Refund Request
        $payment = Payment::where('customer_id', $customer->id)->where('status', 'VERIFIED')->first();
        if ($payment) {
            $refund = $refundService->createRefundRequest(
                payment: $payment,
                amount: 100.00,
                reason: 'Seeded overpayment refund request',
                refundType: 'CASH',
                userId: $user?->id
            );

            $refundService->approveRefundRequest($refund, $user?->id ?? 1);
            $refundService->processRefund($refund, 'VOUCHER-REF-99001', $user?->id);
        }
    }
}
