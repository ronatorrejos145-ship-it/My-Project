<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\ServicePackage;
use App\Http\Requests\StorePromotionRequest;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PromotionController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index()
    {
        Gate::authorize('viewAny', Promotion::class);

        $promotions = Promotion::with('packages')->latest()->paginate(15);
        $packages = ServicePackage::where('status', 'ACTIVE')->get();

        return view('admin.packages.promotions.index', compact('promotions', 'packages'));
    }

    public function store(StorePromotionRequest $request)
    {
        $validated = $request->validated();

        $promo = Promotion::create($validated);

        if ($request->has('packages')) {
            $promo->packages()->sync($request->packages);
        }

        $this->auditLogService->log(
            'PROMOTION_CREATE',
            'Promotions',
            $promo->id,
            null,
            $promo->toArray()
        );

        return redirect()->route('admin.packages.promotions.index')->with('success', 'Promotion created successfully.');
    }
}
