<?php

namespace App\Http\Controllers;

use App\Models\BillableCharge;
use App\Models\BillingException;
use App\Models\BillingPeriod;
use App\Models\BillingProfile;
use App\Models\BillingRun;
use App\Models\ServiceAccount;
use App\Services\BillingPreviewService;
use App\Services\BillingRunService;
use App\Services\ProrationCalculator;
use Illuminate\Http\Request;

class BillingManagementController extends Controller
{
    public function dashboard()
    {
        $this->authorize('viewAny', BillableCharge::class);

        $recentRuns = BillingRun::latest()->take(5)->get();
        $openExceptions = BillingException::where('status', 'OPEN')->latest()->take(5)->get();
        $totalChargesAmount = BillableCharge::where('status', 'CHARGED')->sum('total_amount');
        $totalChargesCount = BillableCharge::where('status', 'CHARGED')->count();

        return view('admin.billing.dashboard', compact('recentRuns', 'openExceptions', 'totalChargesAmount', 'totalChargesCount'));
    }

    public function runs()
    {
        $this->authorize('viewAny', BillingRun::class);

        $runs = BillingRun::latest()->paginate(15);

        return view('admin.billing.runs', compact('runs'));
    }

    public function executeRun(Request $request, BillingRunService $runService)
    {
        $this->authorize('create', BillingRun::class);

        $validated = $request->validate([
            'billing_date' => 'required|date',
            'billing_cycle' => 'required|string',
        ]);

        $run = $runService->createAndExecuteRun($validated['billing_date'], $validated['billing_cycle'], auth()->id());

        return redirect()->route('admin.billing.runs')
            ->with('success', "Billing Run {$run->run_number} executed. Total charges: PHP " . number_format($run->total_amount, 2));
    }

    public function charges()
    {
        $this->authorize('viewAny', BillableCharge::class);

        $charges = BillableCharge::with(['customer', 'serviceAccount', 'subscription'])->latest()->paginate(15);

        return view('admin.billing.charges', compact('charges'));
    }

    public function exceptions()
    {
        $this->authorize('viewAny', BillingException::class);

        $exceptions = BillingException::with(['serviceAccount.customer', 'subscription'])->latest()->paginate(15);

        return view('admin.billing.exceptions', compact('exceptions'));
    }

    public function prorationCalculator(Request $request, ProrationCalculator $calculator)
    {
        $this->authorize('viewAny', BillableCharge::class);

        $result = null;
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'full_price' => 'required|numeric|min:0',
                'service_start' => 'required|date',
                'period_start' => 'required|date',
                'period_end' => 'required|date',
                'basis' => 'required|string|in:CALENDAR_DAY,FIXED_30_DAY',
            ]);

            $result = $calculator->calculateProration(
                (float) $validated['full_price'],
                $validated['service_start'],
                $validated['period_start'],
                $validated['period_end'],
                $validated['basis']
            );
        }

        return view('admin.billing.proration_calculator', compact('result'));
    }
}
