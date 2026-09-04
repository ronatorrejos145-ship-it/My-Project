<?php

namespace Database\Seeders;

use App\Models\NumberSequence;
use Illuminate\Database\Seeder;

class NumberSequenceSeeder extends Seeder
{
    public function run(): void
    {
        $sequences = [
            ['code' => 'CUSTOMER', 'name' => 'Customer Account Number Sequence', 'prefix' => 'CUST-', 'padding' => 6, 'reset_period' => 'NEVER', 'is_branch_aware' => false],
            ['code' => 'ACCOUNT', 'name' => 'Billing Account Number Sequence', 'prefix' => 'ACC-', 'padding' => 6, 'reset_period' => 'NEVER', 'is_branch_aware' => false],
            ['code' => 'APPLICATION', 'name' => 'Online Service Application Number Sequence', 'prefix' => 'APP-', 'padding' => 6, 'reset_period' => 'YEARLY', 'is_branch_aware' => false],
            ['code' => 'INVOICE', 'name' => 'Customer Billing Invoice Number Sequence', 'prefix' => 'INV-', 'padding' => 7, 'reset_period' => 'YEARLY', 'is_branch_aware' => true],
            ['code' => 'RECEIPT', 'name' => 'Official Payment Receipt Number Sequence', 'prefix' => 'OR-', 'padding' => 7, 'reset_period' => 'YEARLY', 'is_branch_aware' => true],
            ['code' => 'PAYMENT', 'name' => 'Payment Voucher Sequence', 'prefix' => 'PAY-', 'padding' => 7, 'reset_period' => 'YEARLY', 'is_branch_aware' => true],
            ['code' => 'WORK_ORDER', 'name' => 'Field Technical Work Order Sequence', 'prefix' => 'WO-', 'padding' => 6, 'reset_period' => 'YEARLY', 'is_branch_aware' => true],
            ['code' => 'TICKET', 'name' => 'Customer Support Ticket Number Sequence', 'prefix' => 'TKT-', 'padding' => 6, 'reset_period' => 'YEARLY', 'is_branch_aware' => false],
            ['code' => 'ASSET', 'name' => 'Asset Tag Number Sequence', 'prefix' => 'AST-', 'padding' => 6, 'reset_period' => 'NEVER', 'is_branch_aware' => false],
            ['code' => 'TOOL', 'name' => 'Technical Tool Number Sequence', 'prefix' => 'TL-', 'padding' => 5, 'reset_period' => 'NEVER', 'is_branch_aware' => false],
            ['code' => 'EMPLOYEE', 'name' => 'Employee Number Sequence', 'prefix' => 'EMP-', 'padding' => 4, 'reset_period' => 'NEVER', 'is_branch_aware' => false],
            ['code' => 'PURCHASE_ORDER', 'name' => 'Purchase Order Sequence', 'prefix' => 'PO-', 'padding' => 6, 'reset_period' => 'YEARLY', 'is_branch_aware' => true],
        ];

        foreach ($sequences as $seq) {
            NumberSequence::firstOrCreate(['code' => $seq['code']], $seq);
        }
    }
}
