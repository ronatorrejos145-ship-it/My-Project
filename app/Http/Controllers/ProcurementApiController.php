<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Services\GoodsReceivingService;
use App\Services\PurchaseOrderService;
use App\Services\PurchaseRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProcurementApiController extends Controller
{
    public function indexRequests(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => PurchaseRequest::with(['requester', 'warehouse', 'items.item'])->latest()->paginate(15),
        ]);
    }

    public function indexOrders(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => PurchaseOrder::with(['supplier', 'warehouse', 'items.item'])->latest()->paginate(15),
        ]);
    }
}
