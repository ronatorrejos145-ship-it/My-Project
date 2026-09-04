<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BranchController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Branch::class);

        $branches = Branch::with(['company', 'manager'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        Gate::authorize('create', Branch::class);
        $companies = Company::where('status', 'ACTIVE')->get();
        return view('admin.branches.create', compact('companies'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Branch::class);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'branch_type' => 'required|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $branch = Branch::create($validated);

        $this->auditLogService->log(
            'BRANCH_CREATE',
            'Branches',
            $branch->id,
            null,
            $branch->toArray()
        );

        return redirect()->route('admin.branches.index')->with('success', 'Branch created successfully.');
    }
}
