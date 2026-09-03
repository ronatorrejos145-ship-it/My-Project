<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_code',
        'legal_name',
        'trade_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'tax_identifier',
        'payment_terms',
        'status',
        'notes',
    ];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_supplier')
            ->withPivot('supplier_item_code', 'supplier_price', 'lead_time_days')
            ->withTimestamps();
    }
}
