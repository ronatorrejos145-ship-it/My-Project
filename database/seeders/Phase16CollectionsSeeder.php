<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use App\Services\CollectionActionService;
use App\Services\DelinquencyEngineService;
use App\Services\ReconnectionService;
use App\Services\SuspensionService;
use App\Services\WriteOffService;
use Illuminate\Database\Seeder;

class Phase16CollectionsSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::where('status', 'ACTIVE')->first();
        if (!$customer) return;

        $user = User::first();
        $delinquencyService = app(DelinquencyEngineService::class);
        $actionService = app(CollectionActionService::class);
        $suspensionService = app(SuspensionService::class);
        $reconnectionService = app(ReconnectionService::class);
        $writeOffService = app(WriteOffService::class);

        // 1. Evaluate Delinquency & Create Collection Profile
        $account = $delinquencyService->evaluateCustomerDelinquency($customer);

        // 2. Record Collection Action
        $actionService->recordCollectionAction(
            account: $account,
            actionType: 'PHONE_CALL',
            notes: 'Seeded initial delinquency follow-up call',
            nextActionDate: now()->addDays(3)->toDateString(),
            collectorUserId: $user?->id
        );

        // 3. Create Promise-to-Pay
        $actionService->createPromiseToPay(
            customer: $customer,
            amount: 500.00,
            promisedDate: now()->addDays(5)->toDateString(),
            notes: 'Subscriber promised payment on Friday',
            userId: $user?->id
        );

        // 4. Create Payment Arrangement
        $arrangement = $actionService->createPaymentArrangement(
            customer: $customer,
            totalAmount: 1500.00,
            downPayment: 300.00,
            installmentsCount: 3,
            startDate: now()->toDateString(),
            notes: 'Seeded 3-month payment arrangement',
            userId: $user?->id
        );
        $actionService->approvePaymentArrangement($arrangement, $user?->id ?? 1);
    }
}
