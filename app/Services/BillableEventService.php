<?php

namespace App\Services;

use App\Models\BillableEvent;
use App\Models\Customer;
use App\Models\ServiceAccount;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class BillableEventService
{
    public function recordEvent(
        string $eventType,
        Customer $customer,
        ServiceAccount $serviceAccount,
        ?Subscription $subscription,
        string $eventDate,
        string $effectiveDate,
        float $quantity,
        float $unitPrice,
        ?string $sourceModule = null,
        ?int $sourceId = null,
        ?array $metadata = null
    ): BillableEvent {
        return DB::transaction(function () use ($eventType, $customer, $serviceAccount, $subscription, $eventDate, $effectiveDate, $quantity, $unitPrice, $sourceModule, $sourceId, $metadata) {
            $calcAmount = round($quantity * $unitPrice, 2);
            $idempotencyKey = "EVT_" . md5("{$eventType}_{$serviceAccount->id}_{$eventDate}_{$effectiveDate}_{$calcAmount}_{$sourceModule}_{$sourceId}");

            $event = BillableEvent::where('idempotency_key', $idempotencyKey)->first();
            if ($event) {
                return $event;
            }

            $event = BillableEvent::create([
                'event_type' => $eventType,
                'customer_id' => $customer->id,
                'service_account_id' => $serviceAccount->id,
                'subscription_id' => $subscription?->id,
                'event_date' => $eventDate,
                'effective_date' => $effectiveDate,
                'source_module' => $sourceModule,
                'source_id' => $sourceId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'calculated_amount' => $calcAmount,
                'metadata' => $metadata,
                'status' => 'PENDING',
                'idempotency_key' => $idempotencyKey,
            ]);

            return $event;
        });
    }
}
