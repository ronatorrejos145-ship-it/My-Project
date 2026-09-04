<?php

namespace App\Services;

use App\Models\TechnicalSurvey;
use App\Models\TechnicalSurveyStatusHistory;
use App\Models\TechnicalSurveyAssignment;
use App\Models\TechnicalSurveyPhoto;
use App\Models\TechnicalSurveyMeasurement;
use App\Models\TechnicalSurveyResponse;
use App\Models\TechnicalSurveyMaterial;
use App\Models\TechnicalSurveyEquipment;
use App\Models\ServiceApplication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TechnicalSurveyService
{
    protected NumberSequenceService $sequenceService;
    protected ServiceabilityEngineService $serviceabilityEngine;
    protected CustomerActivityService $activityService;

    public function __construct(
        NumberSequenceService $sequenceService,
        ServiceabilityEngineService $serviceabilityEngine,
        CustomerActivityService $activityService
    ) {
        $this->sequenceService = $sequenceService;
        $this->serviceabilityEngine = $serviceabilityEngine;
        $this->activityService = $activityService;
    }

    /**
     * Create a new Technical Survey for a Service Application.
     */
    public function createSurveyForApplication(ServiceApplication $application, ?int $technicianId = null, string $priority = 'MEDIUM'): TechnicalSurvey
    {
        return DB::transaction(function () use ($application, $technicianId, $priority) {
            $surveyNumber = $this->sequenceService->getNextNumber('WORK_ORDER');
            $surveyNumber = str_replace('WO-', 'SUR-', $surveyNumber);

            $survey = TechnicalSurvey::create([
                'survey_number' => $surveyNumber,
                'application_id' => $application->id,
                'customer_id' => $application->customer_id,
                'package_id' => $application->service_package_id,
                'package_version_id' => $application->service_package_version_id,
                'technician_id' => $technicianId,
                'survey_type' => 'NEW_INSTALLATION',
                'status' => $technicianId ? 'ASSIGNED' : 'PENDING_ASSIGNMENT',
                'priority' => $priority,
                'created_by' => Auth::id(),
            ]);

            if ($technicianId) {
                TechnicalSurveyAssignment::create([
                    'survey_id' => $survey->id,
                    'new_technician_id' => $technicianId,
                    'assigned_by' => Auth::id(),
                    'notes' => 'Initial survey dispatch',
                ]);
            }

            TechnicalSurveyStatusHistory::create([
                'survey_id' => $survey->id,
                'previous_status' => null,
                'new_status' => $survey->status,
                'reason' => "Technical Survey created for Application #{$application->application_number}",
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);

            return $survey;
        });
    }

    /**
     * Verify technician arrival on site via device GPS coordinates vs customer installation location.
     */
    public function verifyGpsArrival(TechnicalSurvey $survey, float $lat, float $lon, ?float $accuracy = null): TechnicalSurvey
    {
        $targetLat = (float)($survey->application?->latitude ?: $survey->customer?->primaryAddress?->latitude ?: $lat);
        $targetLon = (float)($survey->application?->longitude ?: $survey->customer?->primaryAddress?->longitude ?: $lon);

        $distanceMeters = $this->serviceabilityEngine->calculateHaversineDistance($lat, $lon, $targetLat, $targetLon);

        $arrivalStatus = 'ARRIVED_AT_SITE';
        if ($distanceMeters > 500.0) {
            $arrivalStatus = 'LOCATION_MISMATCH';
        } elseif ($distanceMeters > 100.0) {
            $arrivalStatus = 'NEAR_SITE';
        }

        $survey->update([
            'arrival_latitude' => $lat,
            'arrival_longitude' => $lon,
            'arrival_gps_accuracy' => $accuracy,
            'arrival_verification_status' => $arrivalStatus,
            'arrival_distance_meters' => $distanceMeters,
            'started_at' => $survey->started_at ?: now(),
            'status' => 'ON_SITE',
        ]);

        return $survey;
    }

    /**
     * Upload site photo for technical survey.
     */
    public function storeSurveyPhoto(TechnicalSurvey $survey, UploadedFile $file, string $category = 'FACADE', ?string $caption = null): TechnicalSurveyPhoto
    {
        $originalFilename = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $fileSize = $file->getSize();

        $storedPath = $file->store("private/surveys/{$survey->id}");

        return TechnicalSurveyPhoto::create([
            'survey_id' => $survey->id,
            'category' => $category,
            'original_filename' => $originalFilename,
            'storage_path' => $storedPath,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'caption' => $caption,
            'uploaded_by' => Auth::id(),
        ]);
    }
}
