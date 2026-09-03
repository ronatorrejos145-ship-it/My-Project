<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CompanyController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Company::class);

        $companies = Company::withCount('branches')
            ->when($request->search, function ($query, $search) {
                $query->where('legal_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.companies.index', compact('companies'));
    }

    public function create()
    {
        Gate::authorize('create', Company::class);
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Company::class);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:companies,code',
            'legal_name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'tax_identifier' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $company = Company::create($validated);

        $this->auditLogService->log(
            'COMPANY_CREATE',
            'Companies',
            $company->id,
            null,
            $company->toArray()
        );

        return redirect()->route('admin.companies.index')->with('success', 'Company created successfully.');
    }

    public function edit(Company $company)
    {
        Gate::authorize('update', $company);
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        Gate::authorize('update', $company);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:companies,code,' . $company->id,
            'legal_name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'tax_identifier' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $oldData = $company->toArray();
        $company->update($validated);

        $this->auditLogService->log(
            'COMPANY_UPDATE',
            'Companies',
            $company->id,
            $oldData,
            $company->toArray()
        );

        return redirect()->route('admin.companies.index')->with('success', 'Company updated successfully.');
    }
}
