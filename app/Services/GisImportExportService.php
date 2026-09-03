<?php

namespace App\Services;

use App\Models\GisImport;
use App\Models\NetworkNode;
use App\Models\AccessPoint;
use App\Models\NetworkTower;
use App\Models\DistributionPoint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GisImportExportService
{
    /**
     * Import GPS coordinates from a CSV file.
     * Expected CSV headers: entity_type, code, name, latitude, longitude, extra_field
     */
    public function importCsvCoordinates(UploadedFile $file): GisImport
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        $processed = 0;
        $imported = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $processed++;
                if (count($row) < 5) {
                    $failed++;
                    $errors[] = "Row {$processed}: Insufficient columns.";
                    continue;
                }

                $type = strtoupper(trim($row[0]));
                $code = trim($row[1]);
                $name = trim($row[2]);
                $lat = (float)trim($row[3]);
                $lon = (float)trim($row[4]);

                if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
                    $failed++;
                    $errors[] = "Row {$processed}: Coordinates out of range ({$lat}, {$lon}).";
                    continue;
                }

                if ($type === 'NODE') {
                    NetworkNode::updateOrCreate(
                        ['node_code' => $code],
                        ['name' => $name, 'latitude' => $lat, 'longitude' => $lon, 'status' => 'ACTIVE']
                    );
                    $imported++;
                } elseif ($type === 'AP' || $type === 'ACCESS_POINT') {
                    $node = NetworkNode::first();
                    AccessPoint::updateOrCreate(
                        ['code' => $code],
                        ['name' => $name, 'node_id' => $node?->id ?? 1, 'latitude' => $lat, 'longitude' => $lon, 'status' => 'ACTIVE']
                    );
                    $imported++;
                } elseif ($type === 'TOWER') {
                    NetworkTower::updateOrCreate(
                        ['code' => $code],
                        ['name' => $name, 'latitude' => $lat, 'longitude' => $lon, 'status' => 'ACTIVE']
                    );
                    $imported++;
                } elseif ($type === 'DP' || $type === 'DISTRIBUTION_POINT') {
                    DistributionPoint::updateOrCreate(
                        ['code' => $code],
                        ['name' => $name, 'latitude' => $lat, 'longitude' => $lon, 'status' => 'ACTIVE']
                    );
                    $imported++;
                } else {
                    $failed++;
                    $errors[] = "Row {$processed}: Unknown entity type '{$type}'.";
                }
            }

            fclose($handle);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        return GisImport::create([
            'original_filename' => $file->getClientOriginalName(),
            'file_type' => 'CSV',
            'records_processed' => $processed,
            'records_imported' => $imported,
            'records_failed' => $failed,
            'error_summary' => $errors,
            'imported_by' => Auth::id(),
        ]);
    }
}
