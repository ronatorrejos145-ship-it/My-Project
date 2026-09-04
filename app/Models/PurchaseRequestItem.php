<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'item_id',
        'quantity',
        'estimated_unit_cost',
        'estimated_subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'estimated_unit_cost' => 'decimal:2',
        'estimated_subtotal' => 'decimal:2',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
