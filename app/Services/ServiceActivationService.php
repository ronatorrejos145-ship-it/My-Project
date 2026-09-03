<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\InstallationHandoff;
use App\Models\ServiceAccount;
use App\Models\ServiceContract;
use App\Models\ServiceLocation;
use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Models\Subscription;
use App\Models\SubscriptionStatusHistory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ServiceActivationService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService
    ) {}

    public function activateFromInstallationHandoff(InstallationHandoff $handoff, ?int $userId = null): Subscription
    {
        return DB::transaction(function () use ($handoff, $userId) {
            $handoff = InstallationHandoff::where('id', $handoff->id)->lockForUpdate()->firstOrFail();

            if ($handoff->status === 'ACTIVATED') {
                throw new InvalidArgumentException("Installation handoff #{$handoff->id} is already activated.");
            }

            $customer = Customer::findOrFail($handoff->customer_id);
            $package = ServicePackage::findOrFail($handoff->package_id);
            $packageVersion = ServicePackageVersion::findOrFail($handoff->package_version_id);

            // 1. Create or retrieve Service Account
            $accountNumber = $this->numberSequenceService->getNextNumber('SERVICE_ACCOUNT');
            $serviceAccount = ServiceAccount::create([
                'account_number' => $accountNumber,
                'customer_id' => $customer->id,
                'branch_id' => $customer->branch_id ?? Branch::first()->id,
                'service_type' => 'HOME_INTERNET',
                'status' => 'ACTIVE',
                'activated_at' => now(),
                'primary_location_id' => $handoff->location_id,
                'service_username' => 'sub_' . strtolower($accountNumber),
                'circuit_id' => 'CIR-' . $accountNumber,
                'notes' => 'Activated from installation work order ' . ($handoff->installation?->work_order_number ?? 'N/A'),
            ]);

            // 2. Create Service Location
            ServiceLocation::create([
                'service_account_id' => $serviceAccount->id,
                'location_id' => $handoff->location_id,
                'latitude' => $handoff->latitude,
                'longitude' => $handoff->longitude,
                'is_current' => true,
                'effective_from' => now()->toDateString(),
            ]);

            // 3. Freeze Commercial Package Snapshot into Subscription
            $subNumber = $this->numberSequenceService->getNextNumber('SUBSCRIPTION');
            $subscription = Subscription::create([
                'subscription_number' => $subNumber,
                'service_account_id' => $serviceAccount->id,
                'customer_id' => $customer->id,
                'package_id' => $package->id,
                'package_version_id' => $packageVersion->id,
                'installation_id' => $handoff->installation_id,
                'package_name_snapshot' => $package->name,
                'download_speed_snapshot' => $packageVersion->download_speed,
                'upload_speed_snapshot' => $packageVersion->upload_speed,
                'monthly_price_snapshot' => $packageVersion->monthly_price,
                'billing_cycle_snapshot' => 'MONTHLY',
                'contract_duration_months' => 12,
                'status' => 'ACTIVE',
                'start_date' => now(),
                'next_billing_date' => now()->addMonth(),
                'notes' => 'Commercial snapshot created during activation.',
            ]);

            // 4. Create Service Contract
            $contractNumber = $this->numberSequenceService->getNextNumber('CONTRACT');
            ServiceContract::create([
                'contract_number' => $contractNumber,
                'service_account_id' => $serviceAccount->id,
                'customer_id' => $customer->id,
                'subscription_id' => $subscription->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addYear()->toDateString(),
                'monthly_fee' => $packageVersion->monthly_price,
                'deposit_amount' => $packageVersion->installation_fee ?? 0.00,
                'status' => 'ACTIVE',
                'terms' => 'Standard 12-month lock-in ISP service agreement.',
            ]);

            // Update handoff and customer statuses
            $handoff->update(['status' => 'ACTIVATED']);
            $customer->update(['status' => 'ACTIVE']);

            SubscriptionStatusHistory::create([
                'subscription_id' => $subscription->id,
                'old_status' => 'PENDING',
                'new_status' => 'ACTIVE',
                'changed_by' => $userId,
                'reason' => 'Service activated from completed installation.',
            ]);

            AuditLogService::log(
                'ACTIVATE_SERVICE',
                'services',
                $subscription,
                ['status' => 'PENDING'],
                ['status' => 'ACTIVE', 'account_number' => $accountNumber]
            );

            return $subscription;
        });
    }
}
