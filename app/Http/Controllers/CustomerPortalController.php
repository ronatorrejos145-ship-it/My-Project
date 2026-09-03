<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Customer;
use App\Models\CustomerServiceRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\ServiceAccount;
use App\Models\ServicePackage;
use App\Services\CustomerPortalService;
use App\Services\CustomerServiceRequestService;
use App\Services\InvoicePdfService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerPortalController extends Controller
{
    public function __construct(
        protected CustomerPortalService $portalService,
        protected CustomerServiceRequestService $requestService,
        protected InvoicePdfService $pdfService
    ) {}

    public function dashboard(Request $request)
    {
        $activeAccount = $request->query('account_id');
        $data = $this->portalService->getDashboardData(Auth::user(), $activeAccount);

        if (!$data['has_customer']) {
            return view('customer.portal.no_account');
        }

        return view('customer.portal.dashboard', $data);
    }

    public function services(Request $request)
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        $serviceAccounts = ServiceAccount::where('customer_id', $customer->id)
            ->with(['subscriptions.package', 'branch'])
            ->get();

        $packages = ServicePackage::where('is_active', true)->get();

        return view('customer.portal.services', compact('customer', 'serviceAccounts', 'packages'));
    }

    public function invoices(Request $request)
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        $invoices = Invoice::where('customer_id', $customer->id)
            ->orderBy('due_date', 'desc')
            ->paginate(15);

        return view('customer.portal.invoices', compact('customer', 'invoices'));
    }

    public function showInvoice(Invoice $invoice)
    {
        $this->authorize('viewInvoice', [CustomerPortalPolicy::class, $invoice]);
        $invoice->load(['customer', 'items', 'serviceAccount']);

        return view('customer.portal.invoice_show', compact('invoice'));
    }

    public function downloadInvoicePdf(Invoice $invoice)
    {
        $this->authorize('viewInvoice', [CustomerPortalPolicy::class, $invoice]);
        $pdfContent = $this->pdfService->generateInvoicePdf($invoice);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"Invoice_{$invoice->invoice_number}.pdf\"");
    }

    public function payments(Request $request)
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        $payments = Payment::where('customer_id', $customer->id)
            ->with(['receipt', 'allocations.invoice'])
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return view('customer.portal.payments', compact('customer', 'payments'));
    }

    public function requests(Request $request)
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        $requests = CustomerServiceRequest::where('customer_id', $customer->id)
            ->with(['serviceAccount', 'targetPackage'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $packages = ServicePackage::where('is_active', true)->get();

        return view('customer.portal.requests', compact('customer', 'requests', 'packages'));
    }

    public function storeRequest(Request $request)
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'request_type' => 'required|string',
            'service_account_id' => 'nullable|exists:service_accounts,id',
            'target_package_id' => 'nullable|exists:service_packages,id',
            'notes' => 'nullable|string',
        ]);

        $serviceAccount = !empty($validated['service_account_id']) ? ServiceAccount::find($validated['service_account_id']) : null;
        $targetPackage = !empty($validated['target_package_id']) ? ServicePackage::find($validated['target_package_id']) : null;

        $req = $this->requestService->createRequest(
            customer: $customer,
            requestType: $validated['request_type'],
            serviceAccount: $serviceAccount,
            targetPackage: $targetPackage,
            payload: ['notes' => $validated['notes'] ?? 'Submitted via Customer Portal']
        );

        return redirect()->back()->with('success', "Service Request #{$req->request_number} submitted successfully.");
    }
}
