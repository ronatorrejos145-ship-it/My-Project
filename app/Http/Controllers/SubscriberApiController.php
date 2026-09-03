<?php

namespace App\Http\Controllers;

use App\Models\ServiceAccount;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriberApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ServiceAccount::with(['customer', 'branch', 'currentSubscription.package'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(15),
        ]);
    }

    public function show(ServiceAccount $subscriber): JsonResponse
    {
        $subscriber->load(['customer', 'branch', 'primaryLocation', 'locations', 'subscriptions.package', 'serviceRequests', 'contracts']);

        return response()->json([
            'success' => true,
            'data' => $subscriber,
        ]);
    }
}
