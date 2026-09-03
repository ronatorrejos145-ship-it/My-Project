<?php

namespace App\Http\Controllers;

use App\Models\NetworkTower;
use App\Models\ServiceArea;
use App\Http\Requests\StoreNetworkTowerRequest;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NetworkTowerController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', NetworkTower::class);

        $towers = NetworkTower::with('serviceArea')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        $serviceAreas = ServiceArea::where('status', 'ACTIVE')->get();

        return view('admin.gis.towers.index', compact('towers', 'serviceAreas'));
    }

    public function store(StoreNetworkTowerRequest $request)
    {
        $tower = NetworkTower::create($request->validated());

        $this->auditLogService->log(
            'NETWORK_TOWER_CREATE',
            'NetworkTowers',
            $tower->id,
            null,
            $tower->toArray()
        );

        return redirect()->route('admin.gis.towers.index')->with('success', 'Network tower created.');
    }
}
