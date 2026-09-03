<?php

namespace Database\Seeders;

use App\Models\BillingProfile;
use App\Models\ServiceAccount;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class BillingEngineSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = ServiceAccount::where('status', 'ACTIVE')->get();
        if ($accounts->isEmpty()) {
            return;
        }

        $vat = Tax::where('code', 'VAT12')->first();

        foreach ($accounts as $account) {
            BillingProfile::firstOrCreate(
                ['service_account_id' => $account->id],
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
                    'auto_bill_enabled' => true,
                    'billing_hold' => false,
                ]
            );
        }
    }
}
