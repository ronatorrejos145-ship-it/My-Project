<?php

namespace Database\Seeders;

use App\Models\BillableCharge;
use App\Models\ServiceAccount;
use App\Services\InvoiceFinalizationService;
use App\Services\InvoiceGenerationService;
use Illuminate\Database\Seeder;

class InvoiceAndLedgerSeeder extends Seeder
{
    public function run(): void
    {
        $charges = BillableCharge::where('status', 'CHARGED')->get();
        if ($charges->isEmpty()) {
            return;
        }

        $genService = app(InvoiceGenerationService::class);
        $finService = app(InvoiceFinalizationService::class);

        $accounts = ServiceAccount::whereIn('id', $charges->pluck('service_account_id'))->get();

        foreach ($accounts as $account) {
            try {
                $invoice = $genService->generateForServiceAccount($account);
                $finService->finalizeInvoice($invoice);
            } catch (\Exception $e) {
                // Ignore if already invoiced
            }
        }
    }
}
