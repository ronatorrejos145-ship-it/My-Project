<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Tool;
use App\Models\ToolCheckout;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ToolCheckoutService
{
    public function __construct(protected NumberSequenceService $numberSequenceService) {}

    public function checkoutTool(Tool $tool, Employee $employee, ?string $expectedReturnDate = null, ?string $notes = null, ?int $userId = null): ToolCheckout
    {
        return DB::transaction(function () use ($tool, $employee, $expectedReturnDate, $notes, $userId) {
            /** @var Tool $tool */
            $tool = Tool::where('id', $tool->id)->lockForUpdate()->firstOrFail();

            if ($tool->status !== 'AVAILABLE') {
                throw new InvalidArgumentException("Tool {$tool->tool_code} ({$tool->name}) is currently {$tool->status} and cannot be checked out.");
            }

            $num = $this->numberSequenceService->getNextNumber('TOOL_CHECKOUT');

            $checkout = ToolCheckout::create([
                'checkout_number' => $num,
                'tool_id' => $tool->id,
                'employee_id' => $employee->id,
                'issued_at' => now(),
                'expected_return_at' => $expectedReturnDate ? "{$expectedReturnDate} 17:00:00" : now()->addDays(7),
                'condition_on_issue' => $tool->condition,
                'issued_by' => $userId,
                'notes' => $notes,
            ]);

            $tool->update([
                'status' => 'ISSUED',
                'assigned_employee_id' => $employee->id,
            ]);

            AuditLogService::log(
                'CHECKOUT_TOOL',
                'tools',
                $tool,
                ['status' => 'AVAILABLE'],
                ['status' => 'ISSUED', 'employee_id' => $employee->id]
            );

            return $checkout;
        });
    }

    public function returnTool(ToolCheckout $checkout, string $condition = 'GOOD', ?string $notes = null, ?int $userId = null): ToolCheckout
    {
        return DB::transaction(function () use ($checkout, $condition, $notes, $userId) {
            $checkout = ToolCheckout::where('id', $checkout->id)->lockForUpdate()->firstOrFail();

            if ($checkout->returned_at) {
                throw new InvalidArgumentException("Tool checkout {$checkout->checkout_number} has already been returned.");
            }

            $checkout->update([
                'returned_at' => now(),
                'condition_on_return' => $condition,
                'received_by' => $userId,
                'notes' => $notes ? ($checkout->notes ? $checkout->notes . ' | ' . $notes : $notes) : $checkout->notes,
            ]);

            $tool = Tool::where('id', $checkout->tool_id)->lockForUpdate()->firstOrFail();
            $tool->update([
                'status' => 'AVAILABLE',
                'condition' => $condition,
                'assigned_employee_id' => null,
            ]);

            AuditLogService::log(
                'RETURN_TOOL',
                'tools',
                $tool,
                ['status' => 'ISSUED'],
                ['status' => 'AVAILABLE', 'condition' => $condition]
            );

            return $checkout;
        });
    }
}
