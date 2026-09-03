<?php

namespace App\Http\Controllers;

use App\Models\CollectionAccount;
use App\Models\Customer;
use App\Models\PaymentArrangement;
use App\Models\PromiseToPay;
use App\Models\ReconnectionRequest;
use App\Models\SuspensionRequest;
use App\Models\WriteOffRequest;
use App\Services\CollectionActionService;
use App\Services\DelinquencyEngineService;
use App\Services\ReconnectionService;
use App\Services\SuspensionService;
use App\Services\WriteOffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionsController extends Controller
{
    public function __construct(
        protected DelinquencyEngineService $delinquencyService,
        protected CollectionActionService $actionService,
        protected SuspensionService $suspensionService,
        protected ReconnectionService $reconnectionService,
        protected WriteOffService $writeOffService
    ) {}

    public function dashboard()
    {
        $accounts = CollectionAccount::with(['customer', 'serviceAccount'])
            ->where('total_outstanding_amount', '>', 0)
            ->orderBy('days_overdue', 'desc')
            ->paginate(20);

        $agingBuckets = $this->delinquencyService->calculateArAgingBuckets();

        $stats = [
            'total_overdue' => CollectionAccount::sum('overdue_amount'),
            'suspended_count' => CollectionAccount::where('delinquency_status', 'SUSPENDED')->count(),
            'eligible_suspension' => CollectionAccount::where('delinquency_status', 'SUSPENSION_ELIGIBLE')->count(),
            'promises_active' => PromiseToPay::where('status', 'ACTIVE')->count(),
        ];

        return view('admin.finance.collections.dashboard', compact('accounts', 'agingBuckets', 'stats'));
    }

    public function recordAction(Request $request, CollectionAccount $account)
    {
        $validated = $request->validate([
            'action_type' => 'required|string',
            'notes' => 'nullable|string',
            'next_action_date' => 'nullable|date',
        ]);

        $this->actionService->recordCollectionAction(
            account: $account,
            actionType: $validated['action_type'],
            notes: $validated['notes'] ?? null,
            nextActionDate: $validated['next_action_date'] ?? null,
            collectorUserId: Auth::id()
        );

        return redirect()->back()->with('success', "Collection action recorded for {$account->customer->full_name}.");
    }

    public function promises()
    {
        $promises = PromiseToPay::with(['customer', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $customers = Customer::where('status', 'ACTIVE')->limit(100)->get();

        return view('admin.finance.collections.promises', compact('promises', 'customers'));
    }

    public function storePromise(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'promised_amount' => 'required|numeric|min:0.01',
            'promised_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        $promise = $this->actionService->createPromiseToPay(
            customer: $customer,
            amount: (float)$validated['promised_amount'],
            promisedDate: $validated['promised_date'],
            notes: $validated['notes'] ?? null,
            userId: Auth::id()
        );

        return redirect()->back()->with('success', "Promise-to-Pay #{$promise->promise_number} recorded.");
    }

    public function arrangements()
    {
        $arrangements = PaymentArrangement::with(['customer', 'installments'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $customers = Customer::where('status', 'ACTIVE')->limit(100)->get();

        return view('admin.finance.collections.arrangements', compact('arrangements', 'customers'));
    }

    public function storeArrangement(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'total_amount' => 'required|numeric|min:0.01',
            'down_payment_amount' => 'required|numeric|min:0',
            'installments_count' => 'required|integer|min:1|max:36',
            'start_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        $arrangement = $this->actionService->createPaymentArrangement(
            customer: $customer,
            totalAmount: (float)$validated['total_amount'],
            downPayment: (float)$validated['down_payment_amount'],
            installmentsCount: (int)$validated['installments_count'],
            startDate: $validated['start_date'],
            notes: $validated['notes'] ?? null,
            userId: Auth::id()
        );

        return redirect()->back()->with('success', "Payment Arrangement #{$arrangement->arrangement_number} created.");
    }

    public function approveArrangement(PaymentArrangement $arrangement)
    {
        $this->actionService->approvePaymentArrangement($arrangement, Auth::id());
        return redirect()->back()->with('success', "Arrangement #{$arrangement->arrangement_number} approved and activated.");
    }

    public function suspensions()
    {
        $requests = SuspensionRequest::with(['customer', 'subscription', 'executions'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $eligibleCustomers = Customer::whereHas('collectionAccount', function($q) {
            $q->where('delinquency_status', 'SUSPENSION_ELIGIBLE');
        })->get();

        return view('admin.finance.collections.suspensions', compact('requests', 'eligibleCustomers'));
    }

    public function requestSuspension(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'reason' => 'required|string|max:255',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        $susp = $this->suspensionService->requestSuspension(
            customer: $customer,
            reason: $validated['reason'],
            requestedBy: Auth::id()
        );

        return redirect()->back()->with('success', "Suspension Request #{$susp->request_number} executed cleanly.");
    }

    public function reconnections()
    {
        $requests = ReconnectionRequest::with(['customer', 'subscription', 'executions'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $suspendedCustomers = Customer::whereHas('collectionAccount', function($q) {
            $q->where('delinquency_status', 'SUSPENDED');
        })->get();

        return view('admin.finance.collections.reconnections', compact('requests', 'suspendedCustomers'));
    }

    public function requestReconnection(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'reconnection_fee' => 'nullable|numeric|min:0',
            'waive_fee' => 'nullable|boolean',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        $recon = $this->reconnectionService->requestReconnection(
            customer: $customer,
            reconnectionFee: (float)($validated['reconnection_fee'] ?? 0.00),
            feeWaived: $request->has('waive_fee'),
            waivedBy: $request->has('waive_fee') ? Auth::id() : null,
            requestedBy: Auth::id()
        );

        return redirect()->back()->with('success', "Reconnection Request #{$recon->request_number} executed cleanly.");
    }

    public function writeOffs()
    {
        $requests = WriteOffRequest::with(['customer', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $customers = Customer::where('status', 'ACTIVE')->limit(100)->get();

        return view('admin.finance.collections.write_offs', compact('requests', 'customers'));
    }

    public function requestWriteOff(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        $wo = $this->writeOffService->createWriteOffRequest(
            customer: $customer,
            amount: (float)$validated['amount'],
            reason: $validated['reason'],
            userId: Auth::id()
        );

        return redirect()->back()->with('success', "Write-Off Request #{$wo->write_off_number} submitted for approval.");
    }

    public function approveWriteOff(WriteOffRequest $requestModel)
    {
        $this->writeOffService->approveAndPostWriteOff($requestModel, Auth::id());
        return redirect()->back()->with('success', "Write-off #{$requestModel->write_off_number} approved and posted to ledger.");
    }
}
