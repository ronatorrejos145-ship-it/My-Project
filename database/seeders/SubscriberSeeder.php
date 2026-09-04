<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\InstallationHandoff;
use App\Models\ServiceAccount;
use App\Models\ServiceContract;
use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Models\Subscription;
use App\Services\NumberSequenceService;
use Illuminate\Database\Seeder;

class SubscriberSeeder extends Seeder
{
    public function run(): void
    {
        $handoffs = InstallationHandoff::where('status', 'READY_FOR_ACTIVATION')->get();
        if ($handoffs->isEmpty()) {
            return;
        }

        $numSeq = app(NumberSequenceService::class);

        foreach ($handoffs as $handoff) {
            $customer = Customer::find($handoff->customer_id);
            $package = ServicePackage::find($handoff->package_id);
            $packageVersion = ServicePackageVersion::find($handoff->package_version_id);

            if (!$customer || !$package || !$packageVersion) {
                continue;
            }

            $acctNum = $numSeq->getNextNumber('SERVICE_ACCOUNT');
            $account = ServiceAccount::create([
                'account_number' => $acctNum,
                'customer_id' => $customer->id,
                'branch_id' => $customer->branch_id ?? Branch::first()->id,
                'service_type' => 'HOME_INTERNET',
                'status' => 'ACTIVE',
                'activated_at' => now(),
                'primary_location_id' => $handoff->location_id,
                'service_username' => 'sub_' . strtolower($acctNum),
                'circuit_id' => 'CIR-' . $acctNum,
                'notes' => 'Seeded active subscriber.',
            ]);

            $subNum = $numSeq->getNextNumber('SUBSCRIPTION');
            $subscription = Subscription::create([
                'subscription_number' => $subNum,
                'service_account_id' => $account->id,
                'customer_id' => $customer->id,
                'package_id' => $package->id,
                'package_version_id' => $packageVersion->id,
                'installation_id' => $handoff->installation_id,
                'package_name_snapshot' => $package->name,
                'download_speed_snapshot' => $packageVersion->download_speed,
                'upload_speed_snapshot' => $packageVersion->upload_speed,
                'monthly_price_snapshot' => $packageVersion->monthly_price,
                'billing_cycle_snapshot' => 'MONTHLY',
                'status' => 'ACTIVE',
                'start_date' => now(),
                'next_billing_date' => now()->addMonth(),
            ]);

            ServiceContract::create([
                'contract_number' => $numSeq->getNextNumber('CONTRACT'),
                'service_account_id' => $account->id,
                'customer_id' => $customer->id,
                'subscription_id' => $subscription->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addYear()->toDateString(),
                'monthly_fee' => $packageVersion->monthly_price,
                'status' => 'ACTIVE',
            ]);

            $handoff->update(['status' => 'ACTIVATED']);
            $customer->update(['status' => 'ACTIVE']);
        }
    }
}
