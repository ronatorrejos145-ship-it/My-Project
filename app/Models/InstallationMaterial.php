<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_id',
        'item_id',
        'item_name',
        'unit',
        'planned_qty',
        'reserved_qty',
        'issued_qty',
        'consumed_qty',
        'returned_qty',
        'damaged_qty',
        'variance_qty',
        'notes',
    ];

    protected $casts = [
        'planned_qty' => 'decimal:2',
        'reserved_qty' => 'decimal:2',
        'issued_qty' => 'decimal:2',
        'consumed_qty' => 'decimal:2',
        'returned_qty' => 'decimal:2',
        'damaged_qty' => 'decimal:2',
        'variance_qty' => 'decimal:2',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
