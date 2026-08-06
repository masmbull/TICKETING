<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\View\View;

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
}