<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\TransactionType;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AccountController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Account::class);

        $accounts = Account::with('parentAccount')
            ->when($request->search, function ($query, $search) {
                $query->where('account_name', 'like', "%{$search}%")
                    ->orWhere('account_code', 'like', "%{$search}%");
            })
            ->orderBy('account_code')
            ->paginate(20);

        return view('admin.finance.accounts.index', compact('accounts'));
    }
}
