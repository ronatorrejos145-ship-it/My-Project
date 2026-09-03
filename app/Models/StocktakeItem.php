<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StocktakeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stocktake_id',
        'item_id',
        'system_qty',
        'counted_qty',
        'variance_qty',
        'reason',
    ];

    protected $casts = [
        'system_qty' => 'decimal:2',
        'counted_qty' => 'decimal:2',
        'variance_qty' => 'decimal:2',
    ];

    public function stocktake()
    {
        return $this->belongsTo(Stocktake::class, 'stocktake_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
