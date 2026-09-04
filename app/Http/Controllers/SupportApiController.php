<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerComplaint;
use App\Models\KnowledgeArticle;
use App\Models\SupportIncident;
use App\Models\Ticket;
use App\Services\ComplaintService;
use App\Services\KnowledgeBaseService;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportApiController extends Controller
{
    public function __construct(
        protected TicketService $ticketService,
        protected ComplaintService $complaintService,
        protected KnowledgeBaseService $kbService
    ) {}

    public function indexTickets(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Ticket::class);
        $query = Ticket::with(['customer', 'assignedUser']);

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function showTicket(Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        $customer = Customer::where('user_id', Auth::id())->first();
        $isCustomer = ($customer && $ticket->customer_id === $customer->id && !Auth::user()->hasRole('SUPER_ADMIN'));

        $ticket->load(['customer', 'serviceAccount', 'assignedUser']);

        $messagesQuery = $ticket->messages()->with('user');
        if ($isCustomer) {
            $messagesQuery->where('visibility', 'CUSTOMER_VISIBLE');
        }

        $ticket->setRelation('messages', $messagesQuery->get());

        return response()->json($ticket);
    }

    public function addMessage(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('reply', $ticket);

        $customer = Customer::where('user_id', Auth::id())->first();
        $isCustomer = ($customer && $ticket->customer_id === $customer->id && !Auth::user()->hasRole('SUPER_ADMIN'));

        $validated = $request->validate([
            'message' => 'required|string',
            'visibility' => 'nullable|in:CUSTOMER_VISIBLE,INTERNAL_ONLY',
        ]);

        $visibility = $isCustomer ? 'CUSTOMER_VISIBLE' : ($validated['visibility'] ?? 'CUSTOMER_VISIBLE');
        $authorType = $isCustomer ? 'CUSTOMER' : 'AGENT';

        $msg = $this->ticketService->addMessage(
            ticket: $ticket,
            message: $validated['message'],
            visibility: $visibility,
            userId: Auth::id(),
            authorType: $authorType
        );

        return response()->json($msg);
    }

    public function indexComplaints(Request $request): JsonResponse
    {
        $query = CustomerComplaint::with(['customer', 'assignedOfficer']);
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        return response()->json($query->paginate(20));
    }

    public function indexIncidents(Request $request): JsonResponse
    {
        $incidents = SupportIncident::whereIn('status', ['IDENTIFIED', 'INVESTIGATING', 'MITIGATING'])->get();
        return response()->json($incidents);
    }

    public function searchKnowledgeBase(Request $request): JsonResponse
    {
        $articles = $this->kbService->searchArticles($request->query('query', ''), 'CUSTOMER');
        return response()->json($articles);
    }
}
