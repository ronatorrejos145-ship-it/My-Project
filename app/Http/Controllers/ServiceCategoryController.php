<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use App\Http\Requests\StoreServiceCategoryRequest;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceCategoryController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index()
    {
        Gate::authorize('viewAny', ServiceCategory::class);

        $categories = ServiceCategory::withCount('packages')->latest()->paginate(15);
        return view('admin.packages.categories.index', compact('categories'));
    }

    public function store(StoreServiceCategoryRequest $request)
    {
        $category = ServiceCategory::create($request->validated());

        $this->auditLogService->log(
            'SERVICE_CATEGORY_CREATE',
            'ServiceCategories',
            $category->id,
            null,
            $category->toArray()
        );

        return redirect()->route('admin.packages.categories.index')->with('success', 'Service category created.');
    }
}
