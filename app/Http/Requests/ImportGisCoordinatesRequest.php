<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportGisCoordinatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('gis.import') || $this->user()->hasRole('SUPER_ADMIN') || $this->user()->hasRole('ADMIN') || $this->user()->hasRole('NOC');
    }

    public function rules(): array
    {
        return [
            'gis_file' => 'required|file|mimes:csv,txt|max:10240', // CSV max 10MB
        ];
    }
}
