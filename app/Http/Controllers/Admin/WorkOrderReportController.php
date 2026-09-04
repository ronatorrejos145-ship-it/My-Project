<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WorkOrderReportService;
use Illuminate\Http\Request;

class WorkOrderReportController extends Controller
{
    public function __construct(
        protected WorkOrderReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $metrics = $this->reportService->getExecutiveMetrics($startDate, $endDate);

        return view('admin.maintenance.reports.index', compact('metrics'));
    }
}
