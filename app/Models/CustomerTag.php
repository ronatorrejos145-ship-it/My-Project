<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerTag extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'color_code', 'description', 'status'];

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_customer_tag')->withTimestamps();
    }
}
