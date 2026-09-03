<?php

namespace App\Http\Controllers;

use App\Models\NetworkNode;
use App\Models\NetworkDevice;
use App\Models\AccessPoint;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NetworkNodeController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', NetworkNode::class);

        $nodes = NetworkNode::with(['branch', 'serviceArea', 'parentNode'])
            ->withCount(['accessPoints', 'networkDevices'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('node_code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.network.nodes.index', compact('nodes'));
    }

    public function devices(Request $request)
    {
        Gate::authorize('viewAny', NetworkNode::class);

        $devices = NetworkDevice::with(['node', 'parentDevice'])
            ->when($request->search, function ($query, $search) {
                $query->where('device_name', 'like', "%{$search}%")
                    ->orWhere('device_code', 'like', "%{$search}%")
                    ->orWhere('management_ip', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.network.devices.index', compact('devices'));
    }
}
