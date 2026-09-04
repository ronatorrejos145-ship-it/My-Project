<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationTool extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_id',
        'tool_id',
        'tool_name',
        'issued_at',
        'returned_at',
        'condition_on_return',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function tool()
    {
        return $this->belongsTo(Tool::class, 'tool_id');
    }
}
