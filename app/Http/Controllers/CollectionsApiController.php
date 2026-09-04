<?php

namespace App\Http\Controllers;

use App\Models\CollectionAccount;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionsApiController extends Controller
{
    public function __construct(
        protected DelinquencyEngineService $delinquencyService,
        protected CollectionActionService $actionService,
        protected SuspensionService $suspensionService,
        protected ReconnectionService $reconnectionService,
        protected WriteOffService $writeOffService
    ) {}

    public function indexCollections(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CollectionAccount::class);
        $query = CollectionAccount::with(['customer', 'serviceAccount']);

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('status')) {
            $query->where('delinquency_status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function indexPromises(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CollectionAccount::class);
        $query = PromiseToPay::with(['customer']);
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        return response()->json($query->paginate(20));
    }

    public function indexArrangements(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CollectionAccount::class);
        $query = PaymentArrangement::with(['customer', 'installments']);
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        return response()->json($query->paginate(20));
    }

    public function indexSuspensions(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SuspensionRequest::class);
        $query = SuspensionRequest::with(['customer', 'subscription']);
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        return response()->json($query->paginate(20));
    }

    public function indexReconnections(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ReconnectionRequest::class);
        $query = ReconnectionRequest::with(['customer', 'subscription']);
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        return response()->json($query->paginate(20));
    }

    public function indexWriteOffs(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WriteOffRequest::class);
        $query = WriteOffRequest::with(['customer', 'invoice']);
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        return response()->json($query->paginate(20));
    }
}
