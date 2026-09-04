<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockBalance;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockBalance::with(['item.category', 'warehouse', 'location'])->latest();

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(15),
        ]);
    }

    public function adjust(Request $request, InventoryService $inventoryService): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'transaction_type' => 'required|in:ADJUSTMENT_IN,ADJUSTMENT_OUT,DAMAGE,LOSS',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string',
        ]);

        $item = Item::findOrFail($validated['item_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        $tx = $inventoryService->recordMovement(
            $item,
            $warehouse,
            $validated['transaction_type'],
            (float) $validated['quantity'],
            null,
            null,
            null,
            $validated['reason'],
            null,
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Inventory balance adjusted.',
            'data' => $tx,
        ]);
    }
}
