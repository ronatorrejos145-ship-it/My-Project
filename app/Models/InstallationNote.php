<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_id',
        'author_id',
        'category',
        'note',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
