<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool_id',
        'inspector_id',
        'inspected_at',
        'result',
        'condition',
        'notes',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
    ];

    public function tool()
    {
        return $this->belongsTo(Tool::class, 'tool_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
