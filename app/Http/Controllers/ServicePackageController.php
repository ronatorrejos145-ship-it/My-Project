<?php

namespace App\Http\Controllers;

use App\Models\ServicePackage;
use App\Models\ServiceCategory;
use App\Models\BillingCycle;
use App\Models\Tax;
use App\Models\Branch;
use App\Models\ServiceArea;
use App\Models\PackageFeature;
use App\Models\AssetModel;
use App\Http\Requests\StoreServicePackageRequest;
use App\Http\Requests\UpdateServicePackageRequest;
use App\Services\ServicePackageService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServicePackageController extends Controller
{
    protected ServicePackageService $packageService;
    protected AuditLogService $auditLogService;

    public function __construct(ServicePackageService $packageService, AuditLogService $auditLogService)
    {
        $this->packageService = $packageService;
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', ServicePackage::class);

        $packages = ServicePackage::with(['category', 'billingCycle', 'features', 'activeVersion'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('package_code', 'like', "%{$search}%");
            })
            ->when($request->category_id, function ($query, $catId) {
                $query->where('service_category_id', $catId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15);

        $categories = ServiceCategory::all();

        return view('admin.packages.index', compact('packages', 'categories'));
    }

    public function create()
    {
        Gate::authorize('create', ServicePackage::class);

        $categories = ServiceCategory::where('status', 'ACTIVE')->get();
        $billingCycles = BillingCycle::where('status', 'ACTIVE')->get();
        $taxes = Tax::where('status', 'ACTIVE')->get();
        $branches = Branch::where('status', 'ACTIVE')->get();
        $serviceAreas = ServiceArea::where('status', 'ACTIVE')->get();
        $features = PackageFeature::all();
        $assetModels = AssetModel::where('status', 'ACTIVE')->get();

        return view('admin.packages.create', compact(
            'categories', 'billingCycles', 'taxes', 'branches', 'serviceAreas', 'features', 'assetModels'
        ));
    }

    public function store(StoreServicePackageRequest $request)
    {
        $validated = $request->validated();

        $package = $this->packageService->createPackage(
            $validated,
            $request->input('features', []),
            $request->input('equipment_requirements', []),
            $request->input('branches', []),
            $request->input('service_areas', [])
        );

        $this->auditLogService->log(
            'PACKAGE_CREATE',
            'ServicePackages',
            $package->id,
            null,
            $package->toArray()
        );

        return redirect()->route('admin.packages.show', $package)
            ->with('success', "Service Package {$package->name} created with Version 1.");
    }

    public function show(ServicePackage $package)
    {
        Gate::authorize('view', $package);

        $package->load([
            'category',
            'billingCycle',
            'tax',
            'features',
            'branches',
            'serviceAreas',
            'equipmentRequirements.assetModel',
            'versions.creator',
            'promotions',
        ]);

        return view('admin.packages.show', compact('package'));
    }

    public function edit(ServicePackage $package)
    {
        Gate::authorize('update', $package);

        $categories = ServiceCategory::where('status', 'ACTIVE')->get();
        $branches = Branch::where('status', 'ACTIVE')->get();
        $serviceAreas = ServiceArea::where('status', 'ACTIVE')->get();
        $features = PackageFeature::all();

        return view('admin.packages.edit', compact('package', 'categories', 'branches', 'serviceAreas', 'features'));
    }

    public function update(UpdateServicePackageRequest $request, ServicePackage $package)
    {
        $validated = $request->validated();
        $oldData = $package->toArray();

        $package->update($validated);

        if ($request->has('features')) {
            $package->features()->sync($request->features);
        }
        if ($request->has('branches')) {
            $package->branches()->sync($request->branches);
        }
        if ($request->has('service_areas')) {
            $package->serviceAreas()->sync($request->service_areas);
        }

        $this->auditLogService->log(
            'PACKAGE_UPDATE',
            'ServicePackages',
            $package->id,
            $oldData,
            $package->toArray()
        );

        return redirect()->route('admin.packages.show', $package)
            ->with('success', 'Service package metadata updated.');
    }
}
