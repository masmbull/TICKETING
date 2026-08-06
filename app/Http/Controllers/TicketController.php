<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class TicketController extends Controller
{
    /**
     * Display a listing of the user's tickets with search & filter.
     */
    public function index(Request $request): View
    {
        $query = Ticket::where('user_id', auth()->id())->with(['category', 'subCategory']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'ILIKE', "%{$search}%")
                  ->orWhere('subject', 'ILIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Priority filter
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        // Category filter
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::with('subCategories')->where('is_active', true)->orderBy('name')->get();

        return view('tickets.index', compact('tickets', 'categories'));
    }

    /**
     * Show the form to create a new ticket.
     */
    public function create(): View
    {
        $categories = Category::with('subCategories')->where('is_active', true)->orderBy('name')->get();

        return view('tickets.create', compact('categories'));
    }

    /**
     * Get subcategories for a category (AJAX).
     */
    public function subcategories(Request $request): JsonResponse
    {
        $categoryId = $request->input('category_id');
        $subcategories = SubCategory::where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($subcategories);
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'    => 'nullable|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'subject'        => 'required|max:255',
            'priority'       => 'required|in:low,medium,high,critical',
            'description'    => 'required',
        ]);

        // Generate ticket number: HD-YYYYMMDD-000001
        $today = now()->format('Ymd');
        $lastTicket = Ticket::where('ticket_number', 'like', "HD-{$today}-%")
            ->orderBy('ticket_number', 'desc')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -6);
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        $ticketNumber = "HD-{$today}-{$newNumber}";

        // Create ticket
        $ticket = Ticket::create([
            'ticket_number'  => $ticketNumber,
            'subject'        => $validated['subject'],
            'description'    => $validated['description'],
            'priority'       => $validated['priority'],
            'status'         => 'Open',
            'user_id'        => auth()->id(),
            'category_id'    => $validated['category_id'] ?? null,
            'sub_category_id' => $validated['sub_category_id'] ?? null,
        ]);

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket created successfully. Ticket number: ' . $ticket->ticket_number);
    }

    /**
     * Display the specified ticket.
     */
    public function show(string $id): View
    {
        $ticket = Ticket::with(['category', 'subCategory', 'user', 'comments.user'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Update the status of a ticket.
     */
    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:Open,In Progress,Waiting User,Resolved,Closed',
        ]);

        $ticket = Ticket::where('user_id', auth()->id())->findOrFail($id);

        $oldStatus = $ticket->status;
        $newStatus = $validated['status'];

        // Only update if status actually changed
        if ($oldStatus !== $newStatus) {
            $ticket->update(['status' => $newStatus]);

            // Append timeline entry to session
            $timeline = Session::get('timeline_' . $id, []);
            $timeline[] = [
                'type'       => 'status_change',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'user'       => auth()->user()->name,
                'timestamp'  => now()->toDateTimeString(),
            ];
            Session::put('timeline_' . $id, $timeline);
        }

        return redirect()->route('tickets.show', $id)
            ->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Store a new comment on a ticket.
     */
    public function storeComment(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => 'required|min:3',
        ]);

        $ticket = Ticket::where('user_id', auth()->id())->findOrFail($id);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'comment'   => $validated['comment'],
        ]);

        return redirect()->route('tickets.show', $id)
            ->with('success', 'Comment posted successfully.');
    }
}