<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category_type',
        'icon_path',
        'display_order',
        'status',
    ];

    public function packages()
    {
        return $this->hasMany(ServicePackage::class, 'service_category_id');
    }
}
