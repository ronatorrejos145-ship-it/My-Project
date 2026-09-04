<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'item_code',
        'label',
        'description',
        'response_type',
        'options',
        'is_required',
        'expected_value',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function section()
    {
        return $this->belongsTo(InstallationChecklistSection::class, 'section_id');
    }

    public function responses()
    {
        return $this->hasMany(InstallationChecklistResponse::class, 'checklist_item_id');
    }
}
