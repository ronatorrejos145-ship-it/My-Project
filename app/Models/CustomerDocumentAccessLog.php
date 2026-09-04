<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDocumentAccessLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'document_type',
        'document_id',
        'ip_address',
        'user_agent',
        'accessed_at',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
