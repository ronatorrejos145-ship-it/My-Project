<?php

namespace App\Http\Controllers;

use App\Models\InstallationWorkOrder;
use App\Services\InstallationAcceptanceService;
use Illuminate\Http\Request;

class CustomerInstallationAcceptanceController extends Controller
{
    public function show(InstallationWorkOrder $installation)
    {
        $this->authorize('view', $installation);
        $installation->load(['customer', 'package', 'equipment', 'tests', 'photos']);

        return view('customer.installations.acceptance', compact('installation'));
    }

    public function accept(Request $request, InstallationWorkOrder $installation, InstallationAcceptanceService $acceptanceService)
    {
        $this->authorize('update', $installation);

        $validated = $request->validate([
            'signer_name' => 'required|string|max:255',
            'signer_relationship' => 'required|string',
            'acceptance_status' => 'required|in:ACCEPTED,ACCEPTED_WITH_ISSUES,REJECTED',
            'rejection_reason' => 'required_if:acceptance_status,REJECTED|nullable|string',
            'notes' => 'nullable|string',
        ]);

        $acceptanceService->recordAcceptance(
            $installation,
            $validated['signer_name'],
            $validated['signer_relationship'],
            $validated['acceptance_status'],
            $validated['rejection_reason'] ?? null,
            null,
            $request->ip(),
            $request->userAgent(),
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('success', 'Thank you! Your installation acceptance status has been submitted.');
    }
}
