<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenancePlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plan_code',
        'name',
        'description',
        'maintenance_type',
        'frequency',
        'custom_interval_days',
        'target_asset_type',
        'is_active',
        'required_skills',
        'estimated_duration_minutes',
        'checklist_template_id',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'required_skills' => 'array',
    ];

    public function checklistTemplate()
    {
        return $this->belongsTo(WorkOrderChecklistTemplate::class, 'checklist_template_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function schedules()
    {
        return $this->hasMany(MaintenancePlanSchedule::class);
    }
}
