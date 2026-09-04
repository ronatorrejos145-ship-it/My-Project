<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'old_status',
        'new_status',
        'old_condition',
        'new_condition',
        'old_location',
        'new_location',
        'changed_by',
        'reason',
        'notes',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
