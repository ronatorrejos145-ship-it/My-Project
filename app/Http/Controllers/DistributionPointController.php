<?php

namespace App\Http\Controllers;

use App\Models\DistributionPoint;
use App\Models\NetworkNode;
use App\Http\Requests\StoreDistributionPointRequest;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DistributionPointController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', DistributionPoint::class);

        $dps = DistributionPoint::with('parentNode')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        $nodes = NetworkNode::where('status', 'ACTIVE')->get();

        return view('admin.gis.distribution-points.index', compact('dps', 'nodes'));
    }

    public function store(StoreDistributionPointRequest $request)
    {
        $dp = DistributionPoint::create($request->validated());

        $this->auditLogService->log(
            'DISTRIBUTION_POINT_CREATE',
            'DistributionPoints',
            $dp->id,
            null,
            $dp->toArray()
        );

        return redirect()->route('admin.gis.distribution-points.index')->with('success', 'Fiber distribution point created.');
    }
}
