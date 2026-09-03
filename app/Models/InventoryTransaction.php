<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'item_id',
        'warehouse_id',
        'location_id',
        'transaction_type',
        'quantity',
        'previous_quantity',
        'resulting_quantity',
        'unit',
        'reference_type',
        'reference_id',
        'performed_by',
        'approved_by',
        'reason',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'previous_quantity' => 'decimal:2',
        'resulting_quantity' => 'decimal:2',
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

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
