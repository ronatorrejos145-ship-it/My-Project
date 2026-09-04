<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_number',
        'item_id',
        'warehouse_id',
        'quantity_reserved',
        'reference_type',
        'reference_id',
        'status',
        'expires_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'quantity_reserved' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
