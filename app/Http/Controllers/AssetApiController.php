<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\AssetReceivingService;
use App\Services\AssetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Asset::with(['category', 'model', 'assignedCustomer', 'assignedEmployee'])->latest();

        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }

        if ($request->filled('serial_number')) {
            $query->where('serial_number', $request->serial_number);
        }

        if ($request->filled('mac_address')) {
            $query->where('mac_address', $request->mac_address);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(15),
        ]);
    }

    public function show(Asset $asset): JsonResponse
    {
        $asset->load(['category', 'model', 'assignedCustomer', 'assignedEmployee', 'histories', 'assignments', 'verifications', 'interfaces']);

        return response()->json([
            'success' => true,
            'data' => $asset,
        ]);
    }

    public function store(Request $request, AssetReceivingService $receivingService): JsonResponse
    {
        $validated = $request->validate([
            'asset_category_id' => 'required|exists:asset_categories,id',
            'asset_model_id' => 'nullable|exists:asset_models,id',
            'serial_number' => 'nullable|string|max:100',
            'mac_address' => 'nullable|string|max:50',
            'manufacturer' => 'nullable|string|max:255',
            'purchase_cost' => 'nullable|numeric|min:0',
            'condition' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $asset = $receivingService->receiveAsset($validated, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Asset received successfully.',
            'data' => $asset,
        ], 201);
    }
}
