<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\GoodsReceivingService;
use App\Services\PurchaseOrderService;
use App\Services\PurchaseRequestService;
use Illuminate\Http\Request;

class ProcurementManagementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $purchaseOrders = PurchaseOrder::with(['supplier', 'warehouse'])->latest()->paginate(10);
        $purchaseRequests = PurchaseRequest::with(['requester', 'warehouse'])->latest()->paginate(10);

        return view('admin.procurement.index', compact('purchaseOrders', 'purchaseRequests'));
    }

    public function createPo()
    {
        $this->authorize('create', PurchaseOrder::class);

        $suppliers = Supplier::where('status', 'ACTIVE')->get();
        $warehouses = Warehouse::where('status', 'ACTIVE')->get();

        return view('admin.procurement.create_po', compact('suppliers', 'warehouses'));
    }

    public function storePo(Request $request, PurchaseOrderService $poService)
    {
        $this->authorize('create', PurchaseOrder::class);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.ordered_qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $supplier = Supplier::findOrFail($validated['supplier_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        $po = $poService->createPurchaseOrder($supplier, $warehouse, $validated['items'], null, auth()->id());

        return redirect()->route('admin.procurement.index')
            ->with('success', "Purchase Order {$po->po_number} created and approved.");
    }

    public function receivePo(Request $request, PurchaseOrder $po, GoodsReceivingService $receivingService)
    {
        $this->authorize('create', GoodsReceipt::class);

        $validated = $request->validate([
            'delivery_document_number' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.received_qty' => 'required|numeric|min:0.01',
        ]);

        $receipt = $receivingService->receiveGoods($po, $validated['items'], $validated['delivery_document_number'] ?? null, auth()->id());

        return back()->with('success', "Goods received against PO {$po->po_number} via Receipt {$receipt->receipt_number}.");
    }
}
