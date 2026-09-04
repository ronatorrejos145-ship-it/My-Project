<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianSkill extends Model
{
    use HasFactory;

    protected $fillable = [
        'technician_id',
        'skill_name',
        'proficiency_level',
        'is_certified',
    ];

    protected $casts = [
        'is_certified' => 'boolean',
    ];

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
