<?php

namespace App\Http\Controllers;

use App\Models\NumberSequence;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NumberSequenceController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', NumberSequence::class);

        $sequences = NumberSequence::when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->orderBy('code')
            ->paginate(15);

        return view('admin.number-sequences.index', compact('sequences'));
    }
}
