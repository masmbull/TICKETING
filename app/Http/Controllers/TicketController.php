<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketAttachment;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TicketController extends Controller
{
    /**
     * Display a listing of the user's tickets (My Tickets).
     */
    public function index(Request $request): View
    {
        $query = Ticket::where('user_id', auth()->id())->with(['category', 'subCategory', 'assignee']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'ILIKE', "%{$search}%")
                  ->orWhere('subject', 'ILIKE', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::with('subCategories')->where('is_active', true)->orderBy('name')->get();

        return view('tickets.index', compact('tickets', 'categories'));
    }

    /**
     * All tickets view (Admin/Manager).
     */
    public function allTickets(Request $request): View
    {
        $query = Ticket::with(['category', 'subCategory', 'user', 'assignee']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'ILIKE', "%{$search}%")
                  ->orWhere('subject', 'ILIKE', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::with('subCategories')->where('is_active', true)->orderBy('name')->get();

        return view('tickets.index', compact('tickets', 'categories'));
    }

    /**
     * Assigned tickets (Staff).
     */
    public function assignedTickets(Request $request): View
    {
        $query = Ticket::where('assignee_id', auth()->id())->with(['category', 'subCategory', 'user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'ILIKE', "%{$search}%")
                  ->orWhere('subject', 'ILIKE', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
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

        // Admin/Manager can assign tickets
        $agents = collect();
        if (auth()->user()->isAdmin() || auth()->user()->isManager()) {
            $agents = User::where('is_active', true)
                ->whereIn('role_id', [2, 3]) // Manager + Staff
                ->orderBy('name')
                ->get();
        }

        return view('tickets.create', compact('categories', 'agents'));
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
     * Get category default priority (AJAX).
     */
    public function categoryPriority(Request $request): JsonResponse
    {
        $categoryId = $request->input('category_id');
        $category = Category::find($categoryId);

        return response()->json([
            'default_priority' => $category->default_priority ?? 'medium',
        ]);
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
            'assignee_id'    => 'nullable|exists:users,id',
            'attachments.*'  => 'file|max:10240',
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

        $ticket = Ticket::create([
            'ticket_number'  => $ticketNumber,
            'subject'        => $validated['subject'],
            'description'    => $validated['description'],
            'priority'       => $validated['priority'],
            'status'         => 'Open',
            'user_id'        => auth()->id(),
            'category_id'    => $validated['category_id'] ?? null,
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'assignee_id'    => $validated['assignee_id'] ?? null,
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            $privatePath = storage_path('app/private/attachments');
            if (!is_dir($privatePath)) {
                mkdir($privatePath, 0755, true);
            }

            foreach ($request->file('attachments') as $file) {
                $storedName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($privatePath, $storedName);

                TicketAttachment::create([
                    'ticket_id'        => $ticket->id,
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_filename'  => $storedName,
                    'mime_type'        => $file->getMimeType(),
                    'file_size'        => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket created successfully. Ticket number: ' . $ticket->ticket_number);
    }

    /**
     * Display the specified ticket.
     */
    public function show(string $id): View
    {
        $user = auth()->user();

        // Admin/Manager can see all tickets, others only their own or assigned
        if ($user->isAdmin() || $user->isManager()) {
            $ticket = Ticket::with(['category', 'subCategory', 'user', 'assignee', 'comments.user', 'attachments'])
                ->findOrFail($id);
        } else {
            $ticket = Ticket::with(['category', 'subCategory', 'user', 'assignee', 'comments.user', 'attachments'])
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('assignee_id', $user->id);
                })
                ->findOrFail($id);
        }

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

        $user = auth()->user();
        if ($user->isAdmin() || $user->isManager()) {
            $ticket = Ticket::findOrFail($id);
        } else {
            $ticket = Ticket::where('user_id', $user->id)->findOrFail($id);
        }

        $newStatus = $validated['status'];

        if ($ticket->status !== $newStatus) {
            $updates = ['status' => $newStatus];

            // Track resolved/closed timestamps
            if ($newStatus === 'Resolved' && !$ticket->resolved_at) {
                $updates['resolved_at'] = now();
            }
            if ($newStatus === 'Closed' && !$ticket->closed_at) {
                $updates['closed_at'] = now();
            }

            $ticket->update($updates);
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

        $user = auth()->user();
        if ($user->isAdmin() || $user->isManager()) {
            $ticket = Ticket::findOrFail($id);
        } else {
            $ticket = Ticket::where('user_id', $user->id)
                ->orWhere('assignee_id', $user->id)
                ->findOrFail($id);
        }

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'comment'   => $validated['comment'],
        ]);

        // Track first_response_at for staff
        if ($user->isStaff() || $user->isAdmin() || $user->isManager()) {
            if (!$ticket->first_response_at) {
                $ticket->update(['first_response_at' => now()]);
            }
        }

        return redirect()->route('tickets.show', $id)
            ->with('success', 'Comment posted successfully.');
    }
}