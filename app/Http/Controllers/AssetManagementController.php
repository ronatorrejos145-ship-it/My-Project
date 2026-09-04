<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetModel;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Warehouse;
use App\Services\AssetAssignmentService;
use App\Services\AssetDisposalService;
use App\Services\AssetImportService;
use App\Services\AssetReceivingService;
use App\Services\AssetReplacementService;
use App\Services\AssetRetirementService;
use App\Services\AssetTransferService;
use App\Services\AssetVerificationService;
use App\Services\AssetWarrantyService;
use Illuminate\Http\Request;

class AssetManagementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Asset::class);

        $query = Asset::with(['category', 'model', 'assignedCustomer', 'assignedEmployee'])->latest();

        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('asset_category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('mac_address', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%");
            });
        }

        $assets = $query->paginate(15);
        $categories = AssetCategory::where('status', 'ACTIVE')->get();

        return view('admin.assets.index', compact('assets', 'categories'));
    }

    public function create()
    {
        $this->authorize('create', Asset::class);

        $categories = AssetCategory::where('status', 'ACTIVE')->get();
        $models = AssetModel::where('status', 'ACTIVE')->get();
        $warehouses = Warehouse::where('status', 'ACTIVE')->get();

        return view('admin.assets.create', compact('categories', 'models', 'warehouses'));
    }

    public function store(Request $request, AssetReceivingService $receivingService)
    {
        $this->authorize('create', Asset::class);

        $validated = $request->validate([
            'asset_category_id' => 'required|exists:asset_categories,id',
            'asset_model_id' => 'nullable|exists:asset_models,id',
            'serial_number' => 'nullable|string|max:100',
            'mac_address' => 'nullable|string|max:50',
            'manufacturer' => 'nullable|string|max:255',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'condition' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $asset = $receivingService->receiveAsset($validated, auth()->id());

        return redirect()->route('admin.assets.show', $asset)
            ->with('success', "Asset {$asset->asset_tag} received into inventory successfully.");
    }

    public function show(Asset $asset, AssetWarrantyService $warrantyService)
    {
        $this->authorize('view', $asset);

        $asset->load([
            'category',
            'model',
            'assignedCustomer',
            'assignedEmployee',
            'histories.user',
            'assignments.assigner',
            'verifications.verifier',
            'interfaces',
            'photos',
            'documents',
        ]);

        $warrantyInfo = $warrantyService->getWarrantyStatus($asset);
        $employees = Employee::where('employment_status', 'ACTIVE')->get();
        $customers = Customer::limit(20)->get();

        return view('admin.assets.show', compact('asset', 'warrantyInfo', 'employees', 'customers'));
    }

    public function transfer(Request $request, Asset $asset, AssetTransferService $transferService)
    {
        $this->authorize('transfer', $asset);

        $validated = $request->validate([
            'destination_type' => 'required|string',
            'destination_id' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $transferService->initiateTransfer(
            $asset,
            $validated['destination_type'],
            $validated['destination_id'],
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('success', 'Asset transfer initiated successfully.');
    }

    public function replace(Request $request, Asset $asset, AssetReplacementService $replacementService)
    {
        $this->authorize('update', $asset);

        $validated = $request->validate([
            'new_asset_id' => 'required|exists:assets,id',
            'customer_id' => 'required|exists:customers,id',
            'reason' => 'required|string',
            'old_asset_condition' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $newAsset = Asset::findOrFail($validated['new_asset_id']);
        $customer = Customer::findOrFail($validated['customer_id']);

        $replacementService->replaceEquipment(
            $asset,
            $newAsset,
            $customer,
            null,
            $validated['reason'],
            $validated['old_asset_condition'],
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('success', "Asset {$asset->asset_tag} replaced with {$newAsset->asset_tag}.");
    }

    public function retire(Request $request, Asset $asset, AssetRetirementService $retirementService)
    {
        $this->authorize('retire', $asset);

        $validated = $request->validate([
            'reason' => 'required|string',
            'residual_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $retirementService->retireAsset(
            $asset,
            $validated['reason'],
            (float) ($validated['residual_value'] ?? 0),
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('success', "Asset {$asset->asset_tag} retired.");
    }

    public function dispose(Request $request, Asset $asset, AssetDisposalService $disposalService)
    {
        $this->authorize('retire', $asset);

        $validated = $request->validate([
            'disposal_method' => 'required|string',
            'sale_price' => 'nullable|numeric|min:0',
            'certificate_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $disposalService->disposeAsset(
            $asset,
            $validated['disposal_method'],
            (float) ($validated['sale_price'] ?? 0),
            $validated['certificate_number'] ?? null,
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('success', "Asset {$asset->asset_tag} disposed.");
    }

    public function importCsv(Request $request, AssetImportService $importService)
    {
        $this->authorize('create', Asset::class);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $res = $importService->importCsv($path, auth()->id());

        return back()->with('success', "CSV Import Completed: {$res['imported_count']} asset(s) imported.");
    }
}
