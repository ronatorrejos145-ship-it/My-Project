<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\ServicePackage;
use App\Http\Requests\StoreLeadRequest;
use App\Services\LeadService;
use App\Services\LeadConversionService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LeadController extends Controller
{
    protected LeadService $leadService;
    protected LeadConversionService $conversionService;
    protected AuditLogService $auditLogService;

    public function __construct(LeadService $leadService, LeadConversionService $conversionService, AuditLogService $auditLogService)
    {
        $this->leadService = $leadService;
        $this->conversionService = $conversionService;
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Lead::class);

        $leads = Lead::with(['branch', 'assignedEmployee', 'interestedPackage'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('lead_number', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15);

        $branches = Branch::all();
        $packages = ServicePackage::where('status', 'ACTIVE')->get();

        return view('admin.leads.index', compact('leads', 'branches', 'packages'));
    }

    public function store(StoreLeadRequest $request)
    {
        $validated = $request->validated();

        $lead = $this->leadService->createLead($validated);

        $this->auditLogService->log(
            'LEAD_CREATE',
            'Leads',
            $lead->id,
            null,
            $lead->toArray()
        );

        return redirect()->route('admin.leads.show', $lead)
            ->with('success', "CRM Lead #{$lead->lead_number} created successfully.");
    }

    public function show(Lead $lead)
    {
        Gate::authorize('view', $lead);

        $lead->load(['branch', 'assignedEmployee.user', 'referralCustomer', 'interestedPackage', 'activities.employee.user', 'convertedCustomer']);
        $employees = Employee::with('user')->get();

        return view('admin.leads.show', compact('lead', 'employees'));
    }

    public function convert(Request $request, Lead $lead)
    {
        Gate::authorize('convert', $lead);

        if ($lead->status === 'CONVERTED') {
            return redirect()->back()->with('error', 'Lead has already been converted.');
        }

        $customer = $this->conversionService->convertToCustomer($lead, [
            'customer_type' => $request->input('customer_type', 'RESIDENTIAL'),
        ]);

        $this->auditLogService->log(
            'LEAD_CONVERT',
            'Leads',
            $lead->id,
            ['status' => 'QUALIFIED'],
            ['status' => 'CONVERTED', 'customer_id' => $customer->id]
        );

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', "Lead #{$lead->lead_number} successfully converted into Customer #{$customer->customer_number}.");
    }

    public function addActivity(Request $request, Lead $lead)
    {
        Gate::authorize('update', $lead);

        $request->validate([
            'activity_type' => 'required|in:PHONE_CALL,SMS,EMAIL,VISIT,ONLINE_INQUIRY,FOLLOW_UP,MEETING,OTHER',
            'notes' => 'required|string',
            'next_follow_up_at' => 'nullable|date',
            'outcome' => 'nullable|string|max:255',
        ]);

        $this->leadService->logActivity($lead, [
            'activity_type' => $request->activity_type,
            'notes' => $request->notes,
            'completed_at' => now(),
            'outcome' => $request->outcome,
            'next_follow_up_at' => $request->next_follow_up_at,
            'status' => 'COMPLETED',
        ]);

        return redirect()->route('admin.leads.show', $lead)
            ->with('success', 'Lead follow-up activity logged successfully.');
    }
}
