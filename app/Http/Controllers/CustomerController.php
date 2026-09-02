<?php

namespace App\Http\Controllers;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('customer_number', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('primary_phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        $statuses = CustomerStatus::cases();

        return view('admin.customers.create', compact('statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Customer::class);

        $validated = $request->validate([
            'customer_number' => ['required', 'string', 'max:50', 'unique:customers'],
            'account_number' => ['required', 'string', 'max:50', 'unique:customers'],
            'customer_type' => ['required', 'in:RESIDENTIAL,BUSINESS,CORPORATE,GOVERNMENT'],
            'status' => ['required', 'string'],
            'contact_person' => ['required', 'string', 'max:255'],
            'primary_phone' => ['required', 'string', 'max:20'],
            'secondary_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'installation_address' => ['required', 'string'],
            'billing_address' => ['nullable', 'string'],
        ]);

        $customer = Customer::create($validated);

        AuditLogService::log('customer.created', 'customers', $customer, null, $customer->toArray());

        return redirect()->route('admin.customers.index')->with('success', 'Customer record created successfully.');
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        $statuses = CustomerStatus::cases();

        return view('admin.customers.edit', compact('statuses'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'customer_type' => ['required', 'in:RESIDENTIAL,BUSINESS,CORPORATE,GOVERNMENT'],
            'status' => ['required', 'string'],
            'contact_person' => ['required', 'string', 'max:255'],
            'primary_phone' => ['required', 'string', 'max:20'],
            'secondary_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'installation_address' => ['required', 'string'],
            'billing_address' => ['nullable', 'string'],
        ]);

        $oldValues = $customer->toArray();

        $customer->update($validated);

        AuditLogService::log('customer.updated', 'customers', $customer, $oldValues, $customer->toArray());

        return redirect()->route('admin.customers.index')->with('success', 'Customer record updated successfully.');
    }
}
