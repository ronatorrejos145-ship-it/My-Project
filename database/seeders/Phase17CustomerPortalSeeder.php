<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerPortalProfile;
use App\Models\ServiceAccount;
use App\Models\ServicePackage;
use App\Models\User;
use App\Services\CustomerServiceRequestService;
use Illuminate\Database\Seeder;

class Phase17CustomerPortalSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::where('status', 'ACTIVE')->first();
        if (!$customer) return;

        $user = User::first();
        if ($user && !$customer->user_id) {
            $customer->update(['user_id' => $user->id]);
        }

        CustomerPortalProfile::firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'user_id' => $customer->user_id,
                'preferred_language' => 'en',
                'theme' => 'light',
                'two_factor_enabled' => false,
                'last_login_at' => now(),
            ]
        );

        $requestService = app(CustomerServiceRequestService::class);
        $serviceAccount = ServiceAccount::where('customer_id', $customer->id)->first();
        $package = ServicePackage::first();

        if ($serviceAccount && $package) {
            $requestService->createRequest(
                customer: $customer,
                requestType: 'UPGRADE',
                serviceAccount: $serviceAccount,
                targetPackage: $package,
                payload: ['notes' => 'Seeded customer speed upgrade request']
            );
        }
    }
}
