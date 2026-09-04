<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderDiagnostic extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'technician_id',
        'device_powered',
        'wan_status',
        'lan_status',
        'wifi_status',
        'cable_condition',
        'connector_condition',
        'rx_power_dbm',
        'tx_power_dbm',
        'download_speed_mbps',
        'upload_speed_mbps',
        'latency_ms',
        'packet_loss_percent',
        'diagnosis_notes',
    ];

    protected $casts = [
        'device_powered' => 'boolean',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
