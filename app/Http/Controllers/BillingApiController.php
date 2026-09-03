<?php

namespace App\Http\Controllers;

use App\Models\BillableCharge;
use App\Models\BillingRun;
use App\Models\ServiceAccount;
use App\Services\BillingPreviewService;
use App\Services\BillingRunService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingApiController extends Controller
{
    public function indexCharges(Request $request): JsonResponse
    {
        $query = BillableCharge::with(['customer', 'serviceAccount', 'subscription'])->latest();

        if ($request->filled('service_account_id')) {
            $query->where('service_account_id', $request->service_account_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(15),
        ]);
    }

    public function indexRuns(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => BillingRun::latest()->paginate(15),
        ]);
    }

    public function previewCustomer(Request $request, ServiceAccount $serviceAccount, BillingPreviewService $previewService): JsonResponse
    {
        $validated = $request->validate([
            'preview_date' => 'required|date',
        ]);

        $preview = $previewService->generateCustomerPreview($serviceAccount, $validated['preview_date']);

        return response()->json([
            'success' => true,
            'data' => $preview,
        ]);
    }

    public function executeRun(Request $request, BillingRunService $runService): JsonResponse
    {
        $validated = $request->validate([
            'billing_date' => 'required|date',
            'billing_cycle' => 'required|string',
        ]);

        $run = $runService->createAndExecuteRun($validated['billing_date'], $validated['billing_cycle'], auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Billing run executed successfully.',
            'data' => $run,
        ], 201);
    }
}
