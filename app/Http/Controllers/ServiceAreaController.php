<?php

namespace App\Http\Controllers;

use App\Models\ServiceArea;
use App\Models\Branch;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceAreaController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', ServiceArea::class);

        $serviceAreas = ServiceArea::with('branch')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.service-areas.index', compact('serviceAreas'));
    }
}
