<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('collections:evaluate-delinquency', function () {
    $this->info('Starting automated delinquency evaluation...');
    $customers = \App\Models\Customer::where('status', 'ACTIVE')->get();
    $service = app(\App\Services\DelinquencyEngineService::class);

    foreach ($customers as $customer) {
        $service->evaluateCustomerDelinquency($customer);
    }
    $this->info('Delinquency evaluation completed.');
})->purpose('Evaluates subscriber delinquency states and updates aging profiles daily');
