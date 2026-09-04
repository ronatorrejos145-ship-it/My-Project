<?php

namespace App\Services;

use App\Models\SlaPolicy;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SlaManagementService
{
    public function calculateSlaDeadlines(string $priority, ?SlaPolicy $policy = null): array
    {
        $policy = $policy ?? SlaPolicy::where('priority', $priority)->where('is_active', true)->first();

        $firstResponseMinutes = $policy?->first_response_minutes ?? match($priority) {
            'CRITICAL' => 15,
            'URGENT' => 30,
            'HIGH' => 60,
            'NORMAL' => 240,
            default => 480,
        };

        $resolutionMinutes = $policy?->resolution_minutes ?? match($priority) {
            'CRITICAL' => 120,
            'URGENT' => 240,
            'HIGH' => 480,
            'NORMAL' => 1440,
            default => 2880,
        };

        return [
            'first_response_due' => now()->addMinutes($firstResponseMinutes),
            'resolution_due' => now()->addMinutes($resolutionMinutes),
        ];
    }

    public function evaluateSlaBreaches(): int
    {
        $breachedCount = 0;
        $now = now();

        $tickets = Ticket::whereNotIn('status', ['RESOLVED', 'CLOSED', 'CANCELLED'])
            ->where('is_sla_breached', false)
            ->where(function($q) use ($now) {
                $q->where(function($sub) use ($now) {
                    $sub->whereNull('first_responded_at')
                        ->whereNotNull('first_response_due_at')
                        ->where('first_response_due_at', '<', $now);
                })->orWhere(function($sub) use ($now) {
                    $sub->whereNull('resolved_at')
                        ->whereNotNull('resolution_due_at')
                        ->where('resolution_due_at', '<', $now);
                });
            })->get();

        foreach ($tickets as $tkt) {
            $tkt->update(['is_sla_breached' => true]);
            $breachedCount++;

            AuditLogService::log('SLA_BREACH_DETECTED', 'support', $tkt, null, ['ticket_number' => $tkt->ticket_number]);
        }

        return $breachedCount;
    }
}
