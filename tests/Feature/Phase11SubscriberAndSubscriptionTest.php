<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\InstallationHandoff;
use App\Models\ServiceAccount;
use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PackageChangeService;
use App\Services\RelocationService;
use App\Services\ServiceActivationService;
use App\Services\ServiceLifecycleService;
use App\Services\ServiceRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class Phase11SubscriberAndSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Customer $customer;
    protected ServicePackage $package1;
    protected ServicePackageVersion $version1;
    protected ServicePackage $package2;
    protected ServicePackageVersion $version2;
    protected InstallationHandoff $handoff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders::class);

        $this->adminUser = User::where('email', 'admin@isp.test')->first() ?? User::factory()->create();
        $this->customer = Customer::first();

        $this->package1 = ServicePackage::first();
        $this->version1 = ServicePackageVersion::where('package_id', $this->package1->id)->latest()->first();

        $this->package2 = ServicePackage::skip(1)->first() ?? ServicePackage::factory()->create();
        $this->version2 = ServicePackageVersion::where('package_id', $this->package2->id)->latest()->first() ?? ServicePackageVersion::factory()->create(['package_id' => $this->package2->id]);

        $this->handoff = InstallationHandoff::create([
            'customer_id' => $this->customer->id,
            'package_id' => $this->package1->id,
            'package_version_id' => $this->version1->id,
            'status' => 'READY_FOR_ACTIVATION',
            'handoff_at' => now(),
        ]);
    }

    public function test_activates_subscriber_from_installation_handoff_with_commercial_snapshot(): void
    {
        $activationService = app(ServiceActivationService::class);

        $subscription = $activationService->activateFromInstallationHandoff($this->handoff, $this->adminUser->id);

        $this->assertDatabaseHas('service_accounts', [
            'id' => $subscription->service_account_id,
            'customer_id' => $this->customer->id,
            'status' => 'ACTIVE',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'package_name_snapshot' => $this->package1->name,
            'download_speed_snapshot' => $this->version1->download_speed,
            'monthly_price_snapshot' => $this->version1->monthly_price,
            'status' => 'ACTIVE',
        ]);

        $this->assertEquals('ACTIVATED', $this->handoff->fresh()->status);
        $this->assertEquals('ACTIVE', $this->customer->fresh()->status);
    }

    public function test_executes_commercial_package_upgrade_preserving_history(): void
    {
        $activationService = app(ServiceActivationService::class);
        $subscription = $activationService->activateFromInstallationHandoff($this->handoff, $this->adminUser->id);

        $packageChangeService = app(PackageChangeService::class);
        $packageChangeService->executePackageChange(
            $subscription,
            $this->package2,
            $this->version2,
            'PACKAGE_UPGRADE',
            'Customer requested higher speed',
            $this->adminUser->id
        );

        $this->assertEquals($this->package2->id, $subscription->fresh()->package_id);
        $this->assertEquals($this->package2->name, $subscription->fresh()->package_name_snapshot);

        $this->assertDatabaseHas('subscription_status_histories', [
            'subscription_id' => $subscription->id,
        ]);
    }

    public function test_executes_service_relocation_and_preserves_location_history(): void
    {
        $activationService = app(ServiceActivationService::class);
        $subscription = $activationService->activateFromInstallationHandoff($this->handoff, $this->adminUser->id);
        $account = $subscription->serviceAccount;

        $relocationService = app(RelocationService::class);
        $newLoc = $relocationService->executeRelocation($account, null, null, 14.6001, 120.9855, 'Moved to new street', $this->adminUser->id);

        $this->assertTrue($newLoc->is_current);
        $this->assertDatabaseHas('service_locations', [
            'service_account_id' => $account->id,
            'latitude' => 14.6001,
            'is_current' => true,
        ]);
    }

    public function test_transitions_subscription_and_account_status_lifecycle(): void
    {
        $activationService = app(ServiceActivationService::class);
        $subscription = $activationService->activateFromInstallationHandoff($this->handoff, $this->adminUser->id);

        $lifecycleService = app(ServiceLifecycleService::class);
        $lifecycleService->transitionSubscriptionStatus($subscription, 'SUSPENDED', 'Delinquency suspension', $this->adminUser->id);

        $this->assertEquals('SUSPENDED', $subscription->fresh()->status);
        $this->assertEquals('SUSPENDED', $subscription->serviceAccount->fresh()->status);

        $lifecycleService->transitionSubscriptionStatus($subscription, 'ACTIVE', 'Reconnected', $this->adminUser->id);
        $this->assertEquals('ACTIVE', $subscription->fresh()->status);
    }
}
