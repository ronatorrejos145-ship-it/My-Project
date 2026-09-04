<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'relationship',
        'position',
        'mobile',
        'telephone',
        'email',
        'preferred_contact_method',
        'authorization_level',
        'is_primary',
        'notes',
        'status',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
