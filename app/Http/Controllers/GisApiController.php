<?php

namespace App\Http\Controllers;

use App\Services\GisService;
use App\Services\NearbyInfrastructureService;
use App\Services\GeoJsonService;
use App\Models\ServiceArea;
use App\Models\GisImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GisApiController extends Controller
{
    protected GisService $gisService;
    protected NearbyInfrastructureService $nearbyService;
    protected GeoJsonService $geoJsonService;

    public function __construct(
        GisService $gisService,
        NearbyInfrastructureService $nearbyService,
        GeoJsonService $geoJsonService
    ) {
        $this->gisService = $gisService;
        $this->nearbyService = $nearbyService;
        $this->geoJsonService = $geoJsonService;
    }

    /**
     * Bounding-box spatial map query endpoint (Requires staff authentication and GIS view permission).
     */
    public function viewport(Request $request)
    {
        Gate::authorize('view', GisImport::class);

        $request->validate([
            'north' => 'required|numeric',
            'south' => 'required|numeric',
            'east' => 'required|numeric',
            'west' => 'required|numeric',
        ]);

        $layers = $request->input('layers') ? explode(',', $request->input('layers')) : [];

        $data = $this->gisService->getViewportMapData(
            (float)$request->north,
            (float)$request->south,
            (float)$request->east,
            (float)$request->west,
            $layers
        );

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Find nearby network infrastructure given GPS coordinates (Requires staff authentication).
     */
    public function nearby(Request $request)
    {
        Gate::authorize('view', GisImport::class);

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'nullable|numeric|min:10|max:50000',
        ]);

        $nearby = $this->nearbyService->findNearbyInfrastructure(
            (float)$request->latitude,
            (float)$request->longitude,
            (float)$request->input('radius_meters', 3000.0)
        );

        return response()->json([
            'status' => 'success',
            'data' => $nearby,
        ]);
    }

    /**
     * GeoJSON layer export for active Service Areas.
     */
    public function serviceAreaGeoJson()
    {
        $areas = ServiceArea::whereNotNull('boundary_geojson')->where('status', 'ACTIVE')->get();

        $features = $areas->map(function ($area) {
            return $this->geoJsonService->formatServiceAreaToGeoJson($area);
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
