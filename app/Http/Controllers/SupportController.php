<?php

namespace App\Http\Controllers;

use App\Models\CannedResponse;
use App\Models\Customer;
use App\Models\CustomerComplaint;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\SlaPolicy;
use App\Models\SupportIncident;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Services\ComplaintService;
use App\Services\KnowledgeBaseService;
use App\Services\SlaManagementService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function __construct(
        protected TicketService $ticketService,
        protected SlaManagementService $slaService,
        protected ComplaintService $complaintService,
        protected KnowledgeBaseService $kbService
    ) {}

    public function dashboard()
    {
        $openTicketsCount = Ticket::whereIn('status', ['NEW', 'OPEN', 'ASSIGNED', 'IN_PROGRESS'])->count();
        $breachedCount = Ticket::where('is_sla_breached', true)->count();
        $recentTickets = Ticket::with(['customer', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $activeIncidents = SupportIncident::whereIn('status', ['IDENTIFIED', 'INVESTIGATING', 'MITIGATING'])->get();

        return view('admin.support.dashboard', compact('openTicketsCount', 'breachedCount', 'recentTickets', 'activeIncidents'));
    }

    public function tickets(Request $request)
    {
        $query = Ticket::with(['customer', 'assignedUser', 'assignedDepartment', 'slaPolicy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('customer_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);
        $customers = Customer::where('status', 'ACTIVE')->limit(100)->get();

        return view('admin.support.tickets_index', compact('tickets', 'customers'));
    }

    public function showTicket(Ticket $ticket)
    {
        $ticket->load(['customer', 'serviceAccount', 'assignedUser', 'assignedDepartment', 'slaPolicy', 'messages.user', 'attachments', 'statusHistories.changedBy']);
        $cannedResponses = CannedResponse::where('is_active', true)->get();

        return view('admin.support.ticket_show', compact('ticket', 'cannedResponses'));
    }

    public function storeTicket(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'priority' => 'required|string',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        $tkt = $this->ticketService->createTicket(
            customer: $customer,
            subject: $validated['subject'],
            description: $validated['description'],
            category: $validated['category'],
            priority: $validated['priority'],
            source: 'ADMIN'
        );

        return redirect()->route('admin.support.tickets.show', $tkt)->with('success', "Ticket {$tkt->ticket_number} created.");
    }

    public function replyTicket(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'visibility' => 'required|in:CUSTOMER_VISIBLE,INTERNAL_ONLY',
        ]);

        $this->ticketService->addMessage(
            ticket: $ticket,
            message: $validated['message'],
            visibility: $validated['visibility'],
            userId: Auth::id(),
            authorType: 'AGENT'
        );

        return redirect()->back()->with('success', 'Reply added to ticket conversation.');
    }

    public function updateTicketStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $this->ticketService->updateStatus($ticket, $validated['status'], $validated['reason'] ?? null, Auth::id());

        return redirect()->back()->with('success', "Ticket status updated to {$validated['status']}.");
    }

    public function complaints()
    {
        $complaints = CustomerComplaint::with(['customer', 'ticket', 'assignedOfficer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.support.complaints', compact('complaints'));
    }

    public function incidents()
    {
        $incidents = SupportIncident::with(['leadInvestigator'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.support.incidents', compact('incidents'));
    }

    public function knowledgeBase()
    {
        $articles = KnowledgeArticle::with(['category', 'author'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $categories = KnowledgeCategory::all();

        return view('admin.support.knowledge_base', compact('articles', 'categories'));
    }
}
