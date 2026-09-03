<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'location_id',
        'quantity_on_hand',
        'quantity_reserved',
        'quantity_damaged',
        'quantity_in_transit',
    ];

    protected $casts = [
        'quantity_on_hand' => 'decimal:2',
        'quantity_reserved' => 'decimal:2',
        'quantity_damaged' => 'decimal:2',
        'quantity_in_transit' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function location()
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_id');
    }
}
