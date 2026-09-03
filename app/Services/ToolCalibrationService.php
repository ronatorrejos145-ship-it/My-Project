<?php

namespace App\Services;

use App\Models\Tool;
use App\Models\ToolCalibration;
use Illuminate\Support\Facades\DB;

class ToolCalibrationService
{
    public function recordCalibration(Tool $tool, string $calibrationDate, string $nextDueDate, ?string $provider = null, ?string $certNumber = null, ?string $notes = null): ToolCalibration
    {
        return DB::transaction(function () use ($tool, $calibrationDate, $nextDueDate, $provider, $certNumber, $notes) {
            $calibration = ToolCalibration::create([
                'tool_id' => $tool->id,
                'calibration_date' => $calibrationDate,
                'next_calibration_due' => $nextDueDate,
                'provider_name' => $provider,
                'certificate_number' => $certNumber,
                'status' => 'VALID',
                'notes' => $notes,
            ]);

            AuditLogService::log(
                'CALIBRATE_TOOL',
                'tools',
                $calibration,
                null,
                $calibration->toArray()
            );

            return $calibration;
        });
    }
}
