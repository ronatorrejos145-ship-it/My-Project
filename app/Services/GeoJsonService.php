<?php

namespace App\Services;

class GeoJsonService
{
    /**
     * Validate GeoJSON polygon / multipolygon array structure.
     */
    public function validateGeoJson(array $geojson): bool
    {
        if (empty($geojson['type'])) {
            return false;
        }

        $type = strtoupper($geojson['type']);
        if (!in_array($type, ['FEATURE', 'FEATURECOLLECTION', 'POLYGON', 'MULTIPOLYGON'])) {
            return false;
        }

        if ($type === 'POLYGON' && empty($geojson['coordinates'])) {
            return false;
        }

        return true;
    }

    /**
     * Format a ServiceArea into standard GeoJSON Feature format.
     */
    public function formatServiceAreaToGeoJson($serviceArea): array
    {
        return [
            'type' => 'Feature',
            'properties' => [
                'id' => $serviceArea->id,
                'code' => $serviceArea->code,
                'name' => $serviceArea->name,
                'status' => $serviceArea->status,
                'color' => $serviceArea->color_code,
            ],
            'geometry' => $serviceArea->boundary_geojson['geometry'] ?? $serviceArea->boundary_geojson,
        ];
    }
}
