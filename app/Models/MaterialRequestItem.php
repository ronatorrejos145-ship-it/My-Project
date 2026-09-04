<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_request_id',
        'item_id',
        'requested_qty',
        'issued_qty',
        'unit',
        'notes',
    ];

    protected $casts = [
        'requested_qty' => 'decimal:2',
        'issued_qty' => 'decimal:2',
    ];

    public function materialRequest()
    {
        return $this->belongsTo(MaterialRequest::class, 'material_request_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
