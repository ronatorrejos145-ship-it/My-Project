<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationChecklistResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_id',
        'checklist_item_id',
        'response_value',
        'response_bool',
        'response_photo_path',
        'is_passed',
        'notes',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'response_bool' => 'boolean',
        'is_passed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function item()
    {
        return $this->belongsTo(InstallationChecklistItem::class, 'checklist_item_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
