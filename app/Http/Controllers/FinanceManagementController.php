<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\LedgerTransaction;
use App\Models\ServiceAccount;
use App\Services\FinancialReconciliationService;
use App\Services\InvoiceCancellationService;
use App\Services\InvoiceFinalizationService;
use App\Services\InvoiceGenerationService;
use App\Services\InvoicePdfService;
use App\Services\StatementService;
use Illuminate\Http\Request;

class FinanceManagementController extends Controller
{
    public function invoices(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::with(['customer', 'serviceAccount'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('customer_number', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(15);

        return view('admin.finance.invoices.index', compact('invoices'));
    }

    public function generateForAccount(Request $request, ServiceAccount $serviceAccount, InvoiceGenerationService $generationService)
    {
        $this->authorize('create', Invoice::class);

        $invoice = $generationService->generateForServiceAccount($serviceAccount, null, auth()->id());

        return redirect()->route('admin.finance.invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} generated successfully.");
    }

    public function showInvoice(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['customer', 'serviceAccount', 'lines.charge', 'ledgerTransactions']);

        return view('admin.finance.invoices.show', compact('invoice'));
    }

    public function finalizeInvoice(Invoice $invoice, InvoiceFinalizationService $finalizationService)
    {
        $this->authorize('update', $invoice);

        $finalizationService->finalizeInvoice($invoice, auth()->id());

        return back()->with('success', "Invoice {$invoice->invoice_number} finalized and posted to financial ledger.");
    }

    public function cancelInvoice(Request $request, Invoice $invoice, InvoiceCancellationService $cancellationService)
    {
        $this->authorize('update', $invoice);

        $request->validate(['reason' => 'required|string']);

        $cancellationService->cancelInvoice($invoice, $request->reason, auth()->id());

        return back()->with('success', "Invoice {$invoice->invoice_number} cancelled.");
    }

    public function downloadInvoicePdf(Invoice $invoice, InvoicePdfService $pdfService)
    {
        $this->authorize('view', $invoice);

        $html = $pdfService->generateInvoiceHtml($invoice);

        return response($html)->header('Content-Type', 'text/html');
    }

    public function ledger(Request $request)
    {
        $this->authorize('viewAny', LedgerTransaction::class);

        $query = LedgerTransaction::with(['customer', 'serviceAccount', 'invoice'])->latest();

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        $transactions = $query->paginate(15);

        return view('admin.finance.ledger.index', compact('transactions'));
    }

    public function reconciliation(FinancialReconciliationService $reconciliationService)
    {
        $this->authorize('viewAny', Invoice::class);

        $recon = $reconciliationService->reconcileBillingToInvoices();

        return view('admin.finance.reconciliation', compact('recon'));
    }
}
