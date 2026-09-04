<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'charge_id',
        'charge_type',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'discount_amount',
        'taxable_amount',
        'tax_amount',
        'total_amount',
        'tax_rate',
        'service_period_start',
        'service_period_end',
        'package_id',
        'package_version_id',
        'source_type',
        'source_id',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'service_period_start' => 'date',
        'service_period_end' => 'date',
        'metadata' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function charge()
    {
        return $this->belongsTo(BillableCharge::class, 'charge_id');
    }
}
