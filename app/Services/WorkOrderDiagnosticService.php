<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderDiagnostic;
use Illuminate\Support\Facades\DB;

class WorkOrderDiagnosticService
{
    public function recordDiagnostic(WorkOrder $workOrder, array $data, ?int $technicianId = null): WorkOrderDiagnostic
    {
        return DB::transaction(function () use ($workOrder, $data, $technicianId) {
            return WorkOrderDiagnostic::create([
                'work_order_id' => $workOrder->id,
                'technician_id' => $technicianId,
                'device_powered' => $data['device_powered'] ?? true,
                'wan_status' => $data['wan_status'] ?? null,
                'lan_status' => $data['lan_status'] ?? null,
                'wifi_status' => $data['wifi_status'] ?? null,
                'cable_condition' => $data['cable_condition'] ?? null,
                'connector_condition' => $data['connector_condition'] ?? null,
                'rx_power_dbm' => $data['rx_power_dbm'] ?? null,
                'tx_power_dbm' => $data['tx_power_dbm'] ?? null,
                'download_speed_mbps' => $data['download_speed_mbps'] ?? null,
                'upload_speed_mbps' => $data['upload_speed_mbps'] ?? null,
                'latency_ms' => $data['latency_ms'] ?? null,
                'packet_loss_percent' => $data['packet_loss_percent'] ?? null,
                'diagnosis_notes' => $data['diagnosis_notes'] ?? null,
            ]);
        });
    }
}
