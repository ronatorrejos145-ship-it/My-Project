<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'step_number',
        'item_label',
        'item_type',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(WorkOrderChecklistTemplate::class, 'template_id');
    }
}
