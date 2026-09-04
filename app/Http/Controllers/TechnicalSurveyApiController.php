<?php

namespace App\Http\Controllers;

use App\Models\TechnicalSurvey;
use App\Services\TechnicalSurveyService;
use Illuminate\Http\Request;

class TechnicalSurveyApiController extends Controller
{
    protected TechnicalSurveyService $surveyService;

    public function __construct(TechnicalSurveyService $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    public function verifyGps(Request $request, TechnicalSurvey $survey)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
        ]);

        $updated = $this->surveyService->verifyGpsArrival(
            $survey,
            (float)$request->latitude,
            (float)$request->longitude,
            (float)$request->accuracy
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'arrival_status' => $updated->arrival_verification_status,
                'distance_meters' => $updated->arrival_distance_meters,
            ]
        ]);
    }
}
