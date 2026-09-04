<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\FinancialAdjustment;
use App\Models\RefundRequest;
use App\Services\CreditService;
use App\Services\DiscountService;
use App\Services\FinancialAdjustmentService;
use App\Services\RebateService;
use App\Services\RefundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialAdjustmentApiController extends Controller
{
    public function __construct(
        protected DiscountService $discountService,
        protected CreditService $creditService,
        protected RebateService $rebateService,
        protected RefundService $refundService,
        protected FinancialAdjustmentService $adjustmentService
    ) {}

    public function indexDiscounts(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Discount::class);
        $discounts = Discount::where('is_active', true)->get();
        return response()->json($discounts);
    }

    public function indexCredits(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Credit::class);
        $query = Credit::with(['customer']);
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        return response()->json($query->paginate(20));
    }

    public function indexRefunds(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RefundRequest::class);
        $query = RefundRequest::with(['customer', 'payment']);
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        return response()->json($query->paginate(20));
    }

    public function indexAdjustments(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FinancialAdjustment::class);
        $query = FinancialAdjustment::with(['customer']);
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        return response()->json($query->paginate(20));
    }
}
