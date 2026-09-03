<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CrmDashboardController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Customer::class);

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'ACTIVE')->count();
        $prospects = Customer::whereIn('status', ['PROSPECT', 'LEAD', 'APPLICANT'])->count();
        $suspendedCustomers = Customer::where('status', 'SUSPENDED')->count();

        $totalLeads = Lead::count();
        $newLeads = Lead::where('status', 'NEW')->count();
        $convertedLeads = Lead::where('status', 'CONVERTED')->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        $overdueFollowUps = LeadActivity::where('status', 'PENDING')
            ->where('scheduled_at', '<', now())
            ->count();

        $customersByStatus = Customer::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $recentCustomers = Customer::with('branch')->latest()->take(5)->get();
        $recentLeads = Lead::with('assignedEmployee')->latest()->take(5)->get();

        return view('admin.crm.dashboard', compact(
            'totalCustomers',
            'activeCustomers',
            'prospects',
            'suspendedCustomers',
            'totalLeads',
            'newLeads',
            'convertedLeads',
            'conversionRate',
            'overdueFollowUps',
            'customersByStatus',
            'recentCustomers',
            'recentLeads'
        ));
    }
}
