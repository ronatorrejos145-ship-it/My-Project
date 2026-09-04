<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\LedgerTransaction;
use App\Services\BalanceService;
use App\Services\StatementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceApiController extends Controller
{
    public function indexInvoices(Request $request): JsonResponse
    {
        $query = Invoice::with(['customer', 'serviceAccount', 'lines'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service_account_id')) {
            $query->where('service_account_id', $request->service_account_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(15),
        ]);
    }

    public function showInvoice(Invoice $invoice): JsonResponse
    {
        $invoice->load(['customer', 'serviceAccount', 'lines', 'ledgerTransactions']);

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    public function indexLedger(Request $request): JsonResponse
    {
        $query = LedgerTransaction::with(['customer', 'serviceAccount'])->latest();

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(15),
        ]);
    }

    public function statement(Request $request, StatementService $statementService): JsonResponse
    {
        $request->validate(['customer_id' => 'required|exists:customers,id']);

        $customer = \App\Models\Customer::findOrFail($request->customer_id);
        $statement = $statementService->generateStatement($customer, null, $request->start_date, $request->end_date);

        return response()->json([
            'success' => true,
            'data' => $statement,
        ]);
    }
}
