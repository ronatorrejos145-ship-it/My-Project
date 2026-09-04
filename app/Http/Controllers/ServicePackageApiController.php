<?php

namespace App\Http\Controllers;

use App\Models\ServicePackage;
use Illuminate\Http\Request;

class ServicePackageApiController extends Controller
{
    /**
     * Expose active, public service packages for sales and customer portal application APIs.
     */
    public function index(Request $request)
    {
        $packages = ServicePackage::with(['category', 'features', 'activeVersion'])
            ->where('status', 'ACTIVE')
            ->where('public_visibility', true)
            ->when($request->category, function ($query, $cat) {
                $query->whereHas('category', function ($q) use ($cat) {
                    $q->where('code', $cat);
                });
            })
            ->when($request->package_type, function ($query, $type) {
                $query->where('package_type', $type);
            })
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $packages->map(function ($pkg) {
                return [
                    'id' => $pkg->id,
                    'package_code' => $pkg->package_code,
                    'name' => $pkg->name,
                    'short_name' => $pkg->short_name,
                    'category' => $pkg->category->name ?? null,
                    'package_type' => $pkg->package_type,
                    'technology' => $pkg->technology,
                    'download_speed' => $pkg->download_speed_formatted,
                    'upload_speed' => $pkg->upload_speed_formatted,
                    'monthly_price' => (float)$pkg->base_price,
                    'installation_fee' => (float)$pkg->installation_fee,
                    'contract_period_months' => $pkg->contract_period_months,
                    'features' => $pkg->features->pluck('name'),
                ];
            }),
        ]);
    }
}
