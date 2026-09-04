<?php

namespace App\Http\Controllers;

use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Http\Requests\StorePackageVersionRequest;
use App\Services\PackageVersionService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServicePackageVersionController extends Controller
{
    protected PackageVersionService $versionService;
    protected AuditLogService $auditLogService;

    public function __construct(PackageVersionService $versionService, AuditLogService $auditLogService)
    {
        $this->versionService = $versionService;
        $this->auditLogService = $auditLogService;
    }

    public function create(ServicePackage $package)
    {
        Gate::authorize('createVersion', $package);

        $latestVersion = $package->activeVersion ?: $package->versions->first();

        return view('admin.packages.versions.create', compact('package', 'latestVersion'));
    }

    public function store(StorePackageVersionRequest $request, ServicePackage $package)
    {
        $validated = $request->validated();

        $version = $this->versionService->createVersion($package, $validated);

        $this->auditLogService->log(
            'PACKAGE_VERSION_CREATE',
            'ServicePackageVersions',
            $version->id,
            null,
            $version->toArray()
        );

        return redirect()->route('admin.packages.show', $package)
            ->with('success', "New Package Version {$version->version_number} created and activated.");
    }

    public function compare(ServicePackage $package, ServicePackageVersion $v1, ServicePackageVersion $v2)
    {
        Gate::authorize('view', $package);

        $diffs = $this->versionService->compareVersions($v1, $v2);

        return view('admin.packages.versions.compare', compact('package', 'v1', 'v2', 'diffs'));
    }
}
