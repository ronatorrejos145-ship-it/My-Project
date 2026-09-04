<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationChecklistTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'work_type',
        'package_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sections()
    {
        return $this->hasMany(InstallationChecklistSection::class, 'template_id')->orderBy('sort_order');
    }

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }
}
