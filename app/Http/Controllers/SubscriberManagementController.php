<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InstallationHandoff;
use App\Models\ServiceAccount;
use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Models\ServiceRequest;
use App\Models\Subscription;
use App\Services\PackageChangeService;
use App\Services\RelocationService;
use App\Services\ServiceActivationService;
use App\Services\ServiceLifecycleService;
use App\Services\ServiceRequestService;
use Illuminate\Http\Request;

class SubscriberManagementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ServiceAccount::class);

        $query = ServiceAccount::with(['customer', 'branch', 'currentSubscription.package'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhere('service_username', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('customer_number', 'like', "%{$search}%");
                  });
            });
        }

        $subscribers = $query->paginate(15);
        $pendingHandoffs = InstallationHandoff::where('status', 'READY_FOR_ACTIVATION')->with(['customer', 'package'])->get();

        return view('admin.subscribers.index', compact('subscribers', 'pendingHandoffs'));
    }

    public function show(ServiceAccount $subscriber)
    {
        $this->authorize('view', $subscriber);

        $subscriber->load([
            'customer',
            'branch',
            'primaryLocation',
            'locations.address',
            'subscriptions.package',
            'subscriptions.packageVersion',
            'subscriptions.statusHistories.user',
            'serviceRequests',
            'contracts',
        ]);

        $packages = ServicePackage::where('status', 'ACTIVE')->get();

        return view('admin.subscribers.show', compact('subscriber', 'packages'));
    }

    public function activateHandoff(Request $request, InstallationHandoff $handoff, ServiceActivationService $activationService)
    {
        $this->authorize('create', ServiceAccount::class);

        $subscription = $activationService->activateFromInstallationHandoff($handoff, auth()->id());

        return redirect()->route('admin.subscribers.show', $subscription->service_account_id)
            ->with('success', "Service Account {$subscription->serviceAccount->account_number} successfully activated.");
    }

    public function changePackage(Request $request, Subscription $subscription, PackageChangeService $packageChangeService)
    {
        $this->authorize('update', $subscription->serviceAccount);

        $validated = $request->validate([
            'package_id' => 'required|exists:service_packages,id',
            'package_version_id' => 'required|exists:service_package_versions,id',
            'change_type' => 'required|in:PACKAGE_UPGRADE,PACKAGE_DOWNGRADE',
            'reason' => 'nullable|string',
        ]);

        $package = ServicePackage::findOrFail($validated['package_id']);
        $version = ServicePackageVersion::findOrFail($validated['package_version_id']);

        $packageChangeService->executePackageChange(
            $subscription,
            $package,
            $version,
            $validated['change_type'],
            $validated['reason'] ?? null,
            auth()->id()
        );

        return back()->with('success', "Subscriber plan changed to {$package->name}.");
    }

    public function updateStatus(Request $request, Subscription $subscription, ServiceLifecycleService $lifecycleService)
    {
        $this->authorize('update', $subscription->serviceAccount);

        $validated = $request->validate([
            'status' => 'required|in:ACTIVE,GRACE,SUSPENDED,TERMINATED',
            'reason' => 'nullable|string',
        ]);

        $lifecycleService->transitionSubscriptionStatus(
            $subscription,
            $validated['status'],
            $validated['reason'] ?? null,
            auth()->id()
        );

        return back()->with('success', "Subscriber subscription status updated to {$validated['status']}.");
    }
}
