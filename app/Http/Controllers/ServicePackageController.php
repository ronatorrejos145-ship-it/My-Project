<?php

namespace App\Http\Controllers;

use App\Models\ServicePackage;
use App\Models\BillingCycle;
use App\Models\Tax;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServicePackageController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', ServicePackage::class);

        $packages = ServicePackage::with(['billingCycle', 'tax', 'features', 'versions'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('package_code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.packages.index', compact('packages'));
    }
}
