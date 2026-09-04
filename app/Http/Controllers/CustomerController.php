<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\CustomerTag;
use App\Models\CustomerNote;
use App\Models\ServicePackage;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Services\CustomerService;
use App\Services\CustomerStatusService;
use App\Services\CustomerDuplicateDetectionService;
use App\Services\CustomerActivityService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    protected CustomerService $customerService;
    protected CustomerStatusService $statusService;
    protected CustomerDuplicateDetectionService $duplicateService;
    protected CustomerActivityService $activityService;
    protected AuditLogService $auditLogService;

    public function __construct(
        CustomerService $customerService,
        CustomerStatusService $statusService,
        CustomerDuplicateDetectionService $duplicateService,
        CustomerActivityService $activityService,
        AuditLogService $auditLogService
    ) {
        $this->customerService = $customerService;
        $this->statusService = $statusService;
        $this->duplicateService = $duplicateService;
        $this->activityService = $activityService;
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Customer::class);

        $customers = Customer::with(['branch', 'assignedEmployee', 'tags'])
            ->when($request->search, function ($query, $search) {
                $query->where('customer_number', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('primary_phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->latest()
            ->paginate(15);

        $branches = Branch::all();

        return view('admin.customers.index', compact('customers', 'branches'));
    }

    public function create()
    {
        Gate::authorize('create', Customer::class);

        $branches = Branch::where('status', 'ACTIVE')->get();
        $employees = Employee::with('user')->get();
        $tags = CustomerTag::where('status', 'ACTIVE')->get();

        return view('admin.customers.create', compact('branches', 'employees', 'tags'));
    }

    public function store(StoreCustomerRequest $request)
    {
        $validated = $request->validated();

        // Check for potential duplicates
        $duplicates = $this->duplicateService->findDuplicates($validated);

        if ($duplicates->isNotEmpty() && !$request->has('confirm_duplicate')) {
            $branches = Branch::where('status', 'ACTIVE')->get();
            $employees = Employee::with('user')->get();
            $tags = CustomerTag::where('status', 'ACTIVE')->get();

            return view('admin.customers.create', compact('branches', 'employees', 'tags', 'duplicates'))
                ->withInput()
                ->with('warning', 'Potential duplicate customer records found. Please review before proceeding.');
        }

        $customer = $this->customerService->createCustomer($validated);

        if ($request->has('tags')) {
            $customer->tags()->sync($request->tags);
        }

        $this->auditLogService->log(
            'CUSTOMER_CREATE',
            'Customers',
            $customer->id,
            null,
            $customer->toArray()
        );

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', "Customer Account #{$customer->customer_number} created successfully.");
    }

    public function show(Customer $customer)
    {
        Gate::authorize('view', $customer);

        $customer->load([
            'branch',
            'assignedEmployee.user',
            'referredBy',
            'primaryAddress',
            'installationAddress',
            'billingAddress',
            'contacts',
            'documents.uploader',
            'documents.verifier',
            'notes.creator',
            'tags',
            'statusHistories.user',
            'activities.performer',
            'assignments.previousEmployee',
            'assignments.newEmployee',
            'consents',
            'referrals.referred',
        ]);

        $employees = Employee::with('user')->get();

        return view('admin.customers.show', compact('customer', 'employees'));
    }

    public function edit(Customer $customer)
    {
        Gate::authorize('update', $customer);

        $branches = Branch::where('status', 'ACTIVE')->get();
        $employees = Employee::with('user')->get();
        $tags = CustomerTag::where('status', 'ACTIVE')->get();

        return view('admin.customers.edit', compact('customer', 'branches', 'employees', 'tags'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $validated = $request->validated();
        $oldData = $customer->toArray();

        $customer->update($validated);

        if ($request->has('tags')) {
            $customer->tags()->sync($request->tags);
        }

        $this->activityService->log(
            $customer->id,
            'CUSTOMER_UPDATED',
            'Customer Profile Updated',
            'Customer profile details updated.'
        );

        $this->auditLogService->log(
            'CUSTOMER_UPDATE',
            'Customers',
            $customer->id,
            $oldData,
            $customer->toArray()
        );

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Customer details updated successfully.');
    }

    public function changeStatus(Request $request, Customer $customer)
    {
        Gate::authorize('changeStatus', $customer);

        $request->validate([
            'status' => 'required|in:PROSPECT,LEAD,APPLICANT,PENDING_VERIFICATION,VERIFIED,APPROVED,ACTIVE,TEMPORARILY_INACTIVE,SUSPENDED,TERMINATION_PENDING,TERMINATED,BLACKLISTED,ARCHIVED',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->statusService->transition(
            $customer,
            $request->status,
            $request->reason,
            $request->notes
        );

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', "Customer status transitioned to {$request->status}.");
    }

    public function addNote(Request $request, Customer $customer)
    {
        Gate::authorize('update', $customer);

        $request->validate([
            'note' => 'required|string',
            'visibility' => 'required|in:INTERNAL,CUSTOMER_SERVICE,SALES,TECHNICAL,FINANCE,MANAGEMENT',
        ]);

        CustomerNote::create([
            'customer_id' => $customer->id,
            'note_type' => 'GENERAL',
            'note' => $request->note,
            'visibility' => $request->visibility,
            'created_by' => Auth::id(),
        ]);

        $this->activityService->log(
            $customer->id,
            'NOTE_ADDED',
            'Internal Note Added',
            "Note added with visibility '{$request->visibility}'."
        );

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Note recorded successfully.');
    }
}
