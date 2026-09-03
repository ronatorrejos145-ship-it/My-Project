<?php

namespace App\Services;

use App\Models\InstallationTest;
use App\Models\InstallationWorkOrder;
use Illuminate\Support\Facades\DB;

class InstallationTestService
{
    public function recordTest(
        InstallationWorkOrder $workOrder,
        string $testType,
        string $measuredValue,
        ?string $unit = null,
        ?string $thresholdApplied = null,
        ?string $testSource = null,
        ?string $deviceUsed = null,
        ?string $notes = null,
        ?int $userId = null
    ): InstallationTest {
        return DB::transaction(function () use ($workOrder, $testType, $measuredValue, $unit, $thresholdApplied, $testSource, $deviceUsed, $notes, $userId) {
            $result = $this->evaluateTestResult($testType, $measuredValue, $thresholdApplied);

            $test = InstallationTest::updateOrCreate(
                [
                    'installation_id' => $workOrder->id,
                    'test_type' => $testType,
                ],
                [
                    'measured_value' => $measuredValue,
                    'unit' => $unit,
                    'result' => $result,
                    'threshold_applied' => $thresholdApplied ?? 'Default Rule',
                    'test_source' => $testSource,
                    'device_used' => $deviceUsed,
                    'performed_by' => $userId,
                    'performed_at' => now(),
                    'notes' => $notes,
                ]
            );

            if (in_array($workOrder->status, ['ON_SITE', 'IN_PROGRESS'])) {
                $workOrder->update(['status' => 'TESTING', 'testing_started_at' => $workOrder->testing_started_at ?? now()]);
            }

            return $test;
        });
    }

    public function evaluateTestResult(string $testType, string $measuredValue, ?string $threshold = null): string
    {
        if ($measuredValue === 'NOT_MEASURED' || empty($measuredValue)) {
            return 'NOT_MEASURED';
        }

        $upperVal = strtoupper(trim($measuredValue));
        if (in_array($upperVal, ['PASS', 'SUCCESS', 'OK', 'ONLINE', 'CONNECTED'])) {
            return 'PASS';
        }
        if (in_array($upperVal, ['FAIL', 'FAILED', 'ERROR', 'OFFLINE', 'DISCONNECTED'])) {
            return 'FAIL';
        }

        if (is_numeric($measuredValue)) {
            $num = (float) $measuredValue;
            if ($testType === 'DOWNLOAD' || $testType === 'UPLOAD') {
                return $num >= 5.0 ? 'PASS' : 'WARNING';
            }
            if ($testType === 'LATENCY') {
                return $num <= 100.0 ? 'PASS' : 'WARNING';
            }
            if ($testType === 'PACKET_LOSS') {
                return $num <= 2.0 ? 'PASS' : 'FAIL';
            }
            if ($testType === 'SIGNAL') {
                // Fiber dBm e.g. -18 dBm to -27 dBm
                return ($num >= -27.0 && $num <= -10.0) ? 'PASS' : 'WARNING';
            }
        }

        return 'PASS';
    }
}
