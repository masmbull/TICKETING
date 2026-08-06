<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Display a listing of the user's tickets.
     */
    public function index(): View
    {
        $tickets = Ticket::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show the form to create a new ticket.
     */
    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('tickets.create', compact('categories'));
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'subject' => 'required|max:255',
            'priority' => 'required|in:low,medium,high,critical',
            'description' => 'required',
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
            'ticket_number' => $ticketNumber,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'user_id' => auth()->id(),
            'category_id' => $validated['category_id'] ?? null,
            'sub_category_id' => $validated['sub_category_id'] ?? null,
        ]);

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket created successfully. Ticket number: ' . $ticket->ticket_number);
    }
}