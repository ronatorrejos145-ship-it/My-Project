<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'item_id',
        'requested_qty',
        'dispatched_qty',
        'received_qty',
        'unit',
    ];

    protected $casts = [
        'requested_qty' => 'decimal:2',
        'dispatched_qty' => 'decimal:2',
        'received_qty' => 'decimal:2',
    ];

    public function transfer()
    {
        return $this->belongsTo(InventoryTransfer::class, 'transfer_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
