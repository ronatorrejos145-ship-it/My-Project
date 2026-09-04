<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'item_code',
        'name',
        'category_id',
        'unit',
        'description',
        'unit_cost',
        'minimum_stock',
        'reorder_level',
        'default_supplier_id',
        'status',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'minimum_stock' => 'integer',
        'reorder_level' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function defaultSupplier()
    {
        return $this->belongsTo(Supplier::class, 'default_supplier_id');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'item_supplier')
            ->withPivot('supplier_item_code', 'supplier_price', 'lead_time_days')
            ->withTimestamps();
    }
}
