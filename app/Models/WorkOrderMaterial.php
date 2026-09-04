<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'item_id',
        'warehouse_id',
        'serial_number',
        'required_quantity',
        'issued_quantity',
        'consumed_quantity',
        'returned_quantity',
        'damaged_quantity',
        'unit_cost',
        'total_cost',
        'status',
    ];

    protected $casts = [
        'required_quantity' => 'decimal:2',
        'issued_quantity' => 'decimal:2',
        'consumed_quantity' => 'decimal:2',
        'returned_quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
