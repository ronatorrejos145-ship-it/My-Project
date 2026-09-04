<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'source_warehouse_id',
        'destination_warehouse_id',
        'status',
        'requested_by',
        'approved_by',
        'dispatched_by',
        'received_by',
        'dispatched_at',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(InventoryTransferItem::class, 'transfer_id');
    }
}
