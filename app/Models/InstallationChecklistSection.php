<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationChecklistSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'title',
        'description',
        'sort_order',
    ];

    public function template()
    {
        return $this->belongsTo(InstallationChecklistTemplate::class, 'template_id');
    }

    public function items()
    {
        return $this->hasMany(InstallationChecklistItem::class, 'section_id')->orderBy('sort_order');
    }
}
