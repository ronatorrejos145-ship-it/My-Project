<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Tool;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssetController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Asset::class);

        $assets = Asset::with(['category', 'model', 'assignedEmployee', 'assignedCustomer'])
            ->when($request->search, function ($query, $search) {
                $query->where('asset_tag', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('mac_address', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.assets.index', compact('assets'));
    }

    public function tools(Request $request)
    {
        Gate::authorize('viewAny', Asset::class);

        $tools = Tool::with(['category', 'assignedEmployee'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('tool_code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.assets.tools', compact('tools'));
    }
}
