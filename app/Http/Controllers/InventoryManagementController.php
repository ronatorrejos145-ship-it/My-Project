<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockBalance;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\StockTransferService;
use Illuminate\Http\Request;

class InventoryManagementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', StockBalance::class);

        $query = StockBalance::with(['item.category', 'warehouse', 'location'])->latest();

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        $balances = $query->paginate(15);
        $warehouses = Warehouse::where('status', 'ACTIVE')->get();

        return view('admin.inventory.index', compact('balances', 'warehouses'));
    }

    public function adjustStock(Request $request, InventoryService $inventoryService)
    {
        $this->authorize('create', StockBalance::class);

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'transaction_type' => 'required|in:ADJUSTMENT_IN,ADJUSTMENT_OUT,DAMAGE,LOSS',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string',
        ]);

        $item = Item::findOrFail($validated['item_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        $inventoryService->recordMovement(
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

        return back()->with('success', 'Stock balance adjusted successfully.');
    }
}
