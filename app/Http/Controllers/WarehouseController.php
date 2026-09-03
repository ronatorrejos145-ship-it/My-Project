<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Item;
use App\Models\Supplier;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WarehouseController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Warehouse::class);

        $warehouses = Warehouse::with(['branch', 'manager'])
            ->withCount('locations')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.warehouses.index', compact('warehouses'));
    }

    public function items(Request $request)
    {
        Gate::authorize('viewAny', Warehouse::class);

        $items = Item::with(['category', 'defaultSupplier'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.warehouses.items', compact('items'));
    }

    public function suppliers(Request $request)
    {
        Gate::authorize('viewAny', Warehouse::class);

        $suppliers = Supplier::when($request->search, function ($query, $search) {
                $query->where('legal_name', 'like', "%{$search}%")
                    ->orWhere('supplier_code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.warehouses.suppliers', compact('suppliers'));
    }
}
