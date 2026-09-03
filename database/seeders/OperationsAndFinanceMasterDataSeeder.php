<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\SLADefinition;
use App\Models\Account;
use App\Models\TransactionType;
use Illuminate\Database\Seeder;

class OperationsAndFinanceMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Support Ticket Master Data
        $catNoInternet = TicketCategory::firstOrCreate(['code' => 'TCAT-NO-INT'], ['name' => 'No Internet Connection']);
        $catSlowInternet = TicketCategory::firstOrCreate(['code' => 'TCAT-SLOW-INT'], ['name' => 'Slow Connection / High Latency']);
        $catBilling = TicketCategory::firstOrCreate(['code' => 'TCAT-BILLING'], ['name' => 'Billing & Payment Inquiry']);

        $prioLow = TicketPriority::firstOrCreate(['code' => 'PRIO-LOW'], ['name' => 'Low', 'level' => 1, 'color_code' => '#10B981']);
        $prioMed = TicketPriority::firstOrCreate(['code' => 'PRIO-MED'], ['name' => 'Medium', 'level' => 2, 'color_code' => '#3B82F6']);
        $prioHigh = TicketPriority::firstOrCreate(['code' => 'PRIO-HIGH'], ['name' => 'High / Urgent', 'level' => 3, 'color_code' => '#EF4444']);

        TicketStatus::firstOrCreate(['code' => 'OPEN'], ['name' => 'Open', 'is_closed' => false]);
        TicketStatus::firstOrCreate(['code' => 'IN_PROGRESS'], ['name' => 'In Progress', 'is_closed' => false]);
        TicketStatus::firstOrCreate(['code' => 'RESOLVED'], ['name' => 'Resolved', 'is_closed' => true]);
        TicketStatus::firstOrCreate(['code' => 'CLOSED'], ['name' => 'Closed', 'is_closed' => true]);

        // SLA
        SLADefinition::firstOrCreate(
            ['name' => 'Critical No Internet SLA'],
            [
                'category_id' => $catNoInternet->id,
                'priority_id' => $prioHigh->id,
                'response_time_minutes' => 30,
                'resolution_time_minutes' => 240,
                'business_hours' => '24/7',
                'escalation_level' => 2,
                'status' => 'ACTIVE',
            ]
        );

        // Chart of Accounts Foundation
        $accAR = Account::firstOrCreate(['account_code' => '1100'], ['account_name' => 'Accounts Receivable - Customers', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT', 'status' => 'ACTIVE']);
        $accCash = Account::firstOrCreate(['account_code' => '1010'], ['account_name' => 'Cash on Hand - Cashier', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT', 'status' => 'ACTIVE']);
        $accBank = Account::firstOrCreate(['account_code' => '1020'], ['account_name' => 'Bank Account - Operating', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT', 'status' => 'ACTIVE']);
        $accRevenue = Account::firstOrCreate(['account_code' => '4010'], ['account_name' => 'Internet Subscription Service Revenue', 'account_type' => 'REVENUE', 'normal_balance' => 'CREDIT', 'status' => 'ACTIVE']);

        // Transaction Types
        TransactionType::firstOrCreate(
            ['code' => 'INVOICE'],
            [
                'name' => 'Monthly Internet Subscription Invoice',
                'category' => 'BILLING',
                'default_debit_account_id' => $accAR->id,
                'default_credit_account_id' => $accRevenue->id,
                'description' => 'Standard monthly bill',
            ]
        );

        TransactionType::firstOrCreate(
            ['code' => 'PAYMENT'],
            [
                'name' => 'Customer Payment Collection',
                'category' => 'PAYMENT',
                'default_debit_account_id' => $accCash->id,
                'default_credit_account_id' => $accAR->id,
                'description' => 'Payment received against customer balance',
            ]
        );
    }
}
