<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderEquipmentReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'customer_id',
        'subscription_id',
        'old_asset_id',
        'old_serial_number',
        'old_mac_address',
        'new_asset_id',
        'new_serial_number',
        'new_mac_address',
        'replacement_reason',
        'disposed_or_returned_status',
        'replaced_by_user_id',
        'replaced_at',
    ];

    protected $casts = [
        'replaced_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function oldAsset()
    {
        return $this->belongsTo(Asset::class, 'old_asset_id');
    }

    public function newAsset()
    {
        return $this->belongsTo(Asset::class, 'new_asset_id');
    }

    public function replacedBy()
    {
        return $this->belongsTo(User::class, 'replaced_by_user_id');
    }
}
