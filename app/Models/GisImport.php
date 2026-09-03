<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GisImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_filename',
        'file_type',
        'records_processed',
        'records_imported',
        'records_failed',
        'error_summary',
        'imported_by',
    ];

    protected $casts = [
        'error_summary' => 'array',
        'records_processed' => 'integer',
        'records_imported' => 'integer',
        'records_failed' => 'integer',
    ];

    public function importer()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
