<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stocktake extends Model
{
    use HasFactory;

    protected $fillable = [
        'stocktake_number',
        'warehouse_id',
        'title',
        'stocktake_date',
        'status',
        'conducted_by',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'stocktake_date' => 'date',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(StocktakeItem::class, 'stocktake_id');
    }
}
