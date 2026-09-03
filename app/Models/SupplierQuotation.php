<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierQuotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_number',
        'supplier_id',
        'purchase_request_id',
        'quotation_date',
        'valid_until',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }
}
