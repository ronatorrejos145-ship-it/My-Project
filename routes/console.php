<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\PreventiveMaintenanceService;
use App\Models\WorkOrder;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tickets:evaluate-sla', function () {
    $this->info('Evaluating ticket SLAs...');
})->purpose('Evaluate Ticket SLA breaches');

Artisan::command('maintenance:generate-preventive', function (PreventiveMaintenanceService $service) {
    $count = $service->generateScheduledWorkOrders();
    $this->info("Generated {$count} preventive maintenance work orders.");
})->purpose('Auto-generate preventive maintenance work orders');

Artisan::command('maintenance:evaluate-sla', function () {
    $now = now();
    $breached = WorkOrder::where('is_sla_breached', false)
        ->whereNotIn('status', ['COMPLETED', 'CLOSED', 'CANCELLED'])
        ->where('resolution_due_at', '<', $now)
        ->get();

    foreach ($breached as $wo) {
        $wo->is_sla_breached = true;
        $wo->sla_breached_at = $now;
        $wo->save();
    }

    $this->info("Evaluated SLA breaches. Marked {$breached->count()} work order(s) as breached.");
})->purpose('Evaluate Work Order SLA breaches');

Schedule::command('maintenance:generate-preventive')->daily();
Schedule::command('maintenance:evaluate-sla')->hourly();
