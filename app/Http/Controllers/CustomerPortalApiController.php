<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\CustomerPortalService;
use App\Services\CustomerServiceRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerPortalApiController extends Controller
{
    public function __construct(
        protected CustomerPortalService $portalService,
        protected CustomerServiceRequestService $requestService
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $data = $this->portalService->getDashboardData(Auth::user(), $request->query('account_id'));
        return response()->json($data);
    }

    public function invoices(Request $request): JsonResponse
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        $invoices = Invoice::where('customer_id', $customer->id)
            ->orderBy('due_date', 'desc')
            ->paginate(15);

        return response()->json($invoices);
    }

    public function payments(Request $request): JsonResponse
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        $payments = Payment::where('customer_id', $customer->id)
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return response()->json($payments);
    }

    public function createServiceRequest(Request $request): JsonResponse
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'request_type' => 'required|string',
            'service_account_id' => 'nullable|exists:service_accounts,id',
            'target_package_id' => 'nullable|exists:service_packages,id',
            'notes' => 'nullable|string',
        ]);

        $serviceAccount = !empty($validated['service_account_id']) ? \App\Models\ServiceAccount::find($validated['service_account_id']) : null;
        $targetPackage = !empty($validated['target_package_id']) ? \App\Models\ServicePackage::find($validated['target_package_id']) : null;

        $req = $this->requestService->createRequest(
            customer: $customer,
            requestType: $validated['request_type'],
            serviceAccount: $serviceAccount,
            targetPackage: $targetPackage,
            payload: ['notes' => $validated['notes'] ?? 'API Request']
        );

        return response()->json($req);
    }
}
