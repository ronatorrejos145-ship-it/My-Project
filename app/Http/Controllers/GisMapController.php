<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ServiceApplication;
use App\Models\NetworkNode;
use App\Models\AccessPoint;
use App\Models\NetworkDevice;
use App\Models\NetworkTower;
use App\Models\DistributionPoint;
use App\Models\ServiceArea;
use App\Models\GisImport;
use App\Http\Requests\ImportGisCoordinatesRequest;
use App\Services\GisImportExportService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GisMapController extends Controller
{
    protected GisImportExportService $importService;
    protected AuditLogService $auditLogService;

    public function __construct(GisImportExportService $importService, AuditLogService $auditLogService)
    {
        $this->importService = $importService;
        $this->auditLogService = $auditLogService;
    }

    public function map()
    {
        return view('admin.gis.map');
    }

    public function dashboard()
    {
        $totalCustomers = Customer::count();
        $mappedCustomers = Customer::whereHas('primaryAddress', fn($q) => $q->whereNotNull('latitude')->whereNotNull('longitude'))->count();
        $unmappedCustomers = $totalCustomers - $mappedCustomers;

        $totalApplications = ServiceApplication::count();
        $mappedApplications = ServiceApplication::whereNotNull('latitude')->whereNotNull('longitude')->count();

        $totalNodes = NetworkNode::count();
        $totalAPs = AccessPoint::count();
        $totalNanoboxes = NetworkDevice::where('device_type', 'NANOBOX')->count();
        $totalTowers = NetworkTower::count();
        $totalDPs = DistributionPoint::count();
        $totalServiceAreas = ServiceArea::count();

        return view('admin.gis.dashboard', compact(
            'totalCustomers', 'mappedCustomers', 'unmappedCustomers',
            'totalApplications', 'mappedApplications',
            'totalNodes', 'totalAPs', 'totalNanoboxes', 'totalTowers', 'totalDPs', 'totalServiceAreas'
        ));
    }

    public function importForm()
    {
        $imports = GisImport::with('importer')->latest()->paginate(10);
        return view('admin.gis.import', compact('imports'));
    }

    public function importCsv(ImportGisCoordinatesRequest $request)
    {
        $gisImport = $this->importService->importCsvCoordinates($request->file('gis_file'));

        $this->auditLogService->log(
            'GIS_COORDINATES_IMPORT',
            'GisImports',
            $gisImport->id,
            null,
            ['imported_count' => $gisImport->records_imported]
        );

        return redirect()->route('admin.gis.import.form')
            ->with('success', "GIS Coordinates CSV imported successfully ({$gisImport->records_imported} imported, {$gisImport->records_failed} failed).");
    }
}
