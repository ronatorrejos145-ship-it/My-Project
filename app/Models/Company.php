<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'legal_name',
        'trade_name',
        'registration_number',
        'tax_identifier',
        'email',
        'phone',
        'website',
        'address',
        'logo_path',
        'status',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}
