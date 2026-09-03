<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_id',
        'item_id',
        'received_qty',
        'unit_cost',
    ];

    protected $casts = [
        'received_qty' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
