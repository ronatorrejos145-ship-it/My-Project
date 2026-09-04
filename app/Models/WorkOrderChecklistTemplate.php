<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderChecklistTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'version',
        'work_order_type',
        'description',
        'is_mandatory',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(WorkOrderChecklistItem::class, 'template_id')->orderBy('step_number');
    }
}
