<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'name', 'description'];

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }
}
