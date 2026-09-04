<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'warehouse_id',
        'received_by',
        'received_at',
        'delivery_document_number',
        'inspection_status',
        'notes',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class, 'goods_receipt_id');
    }
}
