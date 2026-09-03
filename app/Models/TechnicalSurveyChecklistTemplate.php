<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalSurveyChecklistTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'technology', 'status'];

    public function items()
    {
        return $this->hasMany(TechnicalSurveyChecklistItem::class, 'template_id')->orderBy('display_order');
    }
}
