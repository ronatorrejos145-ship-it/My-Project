<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\ToolCheckout;
use App\Services\ToolCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ToolApiController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Tool::with(['category', 'assignedEmployee'])->latest()->paginate(15),
        ]);
    }
}
