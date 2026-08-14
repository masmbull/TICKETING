<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketAttachment;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\SlaPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
                  ->orWhere('description', 'ILIKE', "%{$search}%");
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
        $staffUsers = $this->supportUsers();
        $subCategoriesByCategory = Category::with('subCategories')->get()->mapWithKeys(fn($cat) => [$cat->id => $cat->subCategories->map(fn($sub) => ['id' => $sub->id, 'name' => $sub->name])]);

        return view('tickets.index', compact('tickets', 'categories', 'staffUsers', 'subCategoriesByCategory'));
    }

    /**
     * All tickets view (Admin/Manager only).
     */
    public function allTickets(Request $request): View
    {
        if (!in_array(auth()->user()->role?->slug, ['admin', 'manager'])) {
            abort(403, 'Unauthorized. Only Admin or Manager can view all tickets.');
        }

        $query = Ticket::with(['category', 'subCategory', 'user', 'assignee']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhereHas('user', function ($qq) use ($search) {
                      $qq->where('name', 'ILIKE', "%{$search}%")
                         ->orWhere('email', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('assignee', function ($qq) use ($search) {
                      $qq->where('name', 'ILIKE', "%{$search}%")
                         ->orWhere('email', 'ILIKE', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        if ($assignee = $request->input('assignee')) {
            $query->where('assignee_id', $assignee);
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::with('subCategories')->where('is_active', true)->orderBy('name')->get();
        $staffUsers = $this->supportUsers();
        $subCategoriesByCategory = Category::with('subCategories')->get()->mapWithKeys(fn($cat) => [$cat->id => $cat->subCategories->map(fn($sub) => ['id' => $sub->id, 'name' => $sub->name])]);

        return view('tickets.index', compact('tickets', 'categories', 'staffUsers', 'subCategoriesByCategory'));
    }

    /**
     * Assigned tickets (Staff).
     */
    public function assignedTickets(Request $request): View
    {
        $query = Ticket::where('assignee_id', auth()->id())->with(['category', 'subCategory', 'user', 'assignee']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhereHas('user', function ($qq) use ($search) {
                      $qq->where('name', 'ILIKE', "%{$search}%");
                  });
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
        $staffUsers = $this->supportUsers();

        return view('tickets.index', compact('tickets', 'categories', 'staffUsers'));
    }

    /**
     * Show the form to create a new ticket.
     */
    public function create(): View
    {
        $categories = Category::with('subCategories')->where('is_active', true)->orderBy('name')->get();
        $slaPolicies = \App\Models\SlaPolicy::where('is_active', true)->orderBy('name')->get();
        $staffUsers = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['admin', 'manager', 'staff']);
        })->orderBy('name')->get();
        $subCategoriesByCategory = Category::with('subCategories')->get()->mapWithKeys(fn($cat) => [$cat->id => $cat->subCategories->map(fn($sub) => ['id' => $sub->id, 'name' => $sub->name])]);

        return view('tickets.create', compact('categories', 'slaPolicies', 'staffUsers', 'subCategoriesByCategory'));
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
        // Normalize a single non-array file upload into an array so the
        // attachments.* validation rules always run and reject bad files.
        // NOTE: we must read/write the raw Symfony FileBag ($request->files)
        // BEFORE calling $request->file()/hasFile()/allFiles(), because
        // Laravel caches the converted files in $convertedFiles and a scalar
        // entry would otherwise stay scalar, making the 'array' rule fail.
        $rawAttachment = $request->files->get('attachments');
        if ($rawAttachment && !is_array($rawAttachment)) {
            $request->files->set('attachments', [$rawAttachment]);
        }

        $isSupport = auth()->user()->canManageTickets();

        $validated = $request->validate([
            'category_id'     => 'nullable|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'priority'        => $isSupport ? 'required|in:low,medium,high,critical' : 'nullable|in:low,medium,high,critical',
            'description'     => 'required',
            'assignee_id'     => $isSupport ? 'nullable|exists:users,id' : 'nullable',
            'sla_policy_id'   => 'nullable|exists:sla_policies,id',
            'attachments'     => 'nullable|array',
            'attachments.*'   => 'file|max:10240|mimes:png,jpg,jpeg,pdf,zip',
        ]);

        $category = isset($validated['category_id']) ? Category::find($validated['category_id']) : null;

        // Employees never see/set priority, assignee or SLA — default the
        // priority to the category default (falling back to Medium) and
        // auto-assign the matching SLA policy.
        $priority = $validated['priority'] ?? ($category?->default_priority ?? 'medium');
        $slaPriority = $this->resolveSlaPriority($validated['sla_policy_id'] ?? null, $category, $priority);
        $assigneeId = $isSupport ? ($validated['assignee_id'] ?? null) : null;

        // Generate ticket number: ITSUP-YYYYMMDD-NNNNN
        $today = now()->format('Ymd');
        $lastTicket = Ticket::where('ticket_number', 'like', "ITSUP-{$today}-%")
            ->orderBy('ticket_number', 'desc')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        $ticketNumber = "ITSUP-{$today}-{$newNumber}";

         $slaPolicy = $this->resolveSlaPolicyModel($validated['sla_policy_id'] ?? null, $category, $priority);
        $slaStartedAt = $slaPolicy ? now() : null;
        $slaDeadline = $slaPolicy ? $slaStartedAt->copy()->addHours($slaPolicy->resolution_hours) : null;

        $ticket = Ticket::create([
            'ticket_number'   => $ticketNumber,
            'description'     => $validated['description'],
            'priority'        => $priority,
            'sla_priority'    => $slaPriority,
            'sla_started_at'  => $slaStartedAt,
            'sla_deadline'    => $slaDeadline,
            'status'          => 'Waiting Confirmation',
            'user_id'         => auth()->id(),
            'category_id'     => $validated['category_id'] ?? null,
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'assignee_id'     => $assigneeId,
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            $privatePath = storage_path('app/private/attachments');
            if (!is_dir($privatePath)) {
                mkdir($privatePath, 0755, true);
            }

            $files = $request->file('attachments');
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
                $mimeType = $file->getMimeType();
                $fileSize = $file->getSize();
                $storedName = uniqid() . '_' . time() . '.' . $extension;

                $file->move($privatePath, $storedName);

                TicketAttachment::create([
                    'ticket_id'         => $ticket->id,
                    'original_filename' => $originalName,
                    'stored_filename'   => $storedName,
                    'mime_type'         => $mimeType,
                    'file_size'         => $fileSize,
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
            $ticket = Ticket::with(['category', 'subCategory', 'user', 'assignee', 'completedBy', 'comments.user', 'attachments'])
                ->findOrFail($id);
        } else {
            $ticket = Ticket::with(['category', 'subCategory', 'user', 'assignee', 'completedBy', 'comments.user', 'attachments'])
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('assignee_id', $user->id);

                    // IT Support may also browse the unassigned queue.
                    if ($user->isStaff()) {
                        $q->orWhereNull('assignee_id');
                    }
                })
                ->findOrFail($id);
        }

        $staffUsers = $this->supportUsers();

        return view('tickets.show', compact('ticket', 'staffUsers'));
    }

    /**
     * Update the status of a ticket.
     *
     * Only IT Support members and management may move tickets through the
     * workflow. Backend enforcement for Sprint 3.1:
     *   - Waiting Confirmation -> In Progress requires a Problem Analysis and
     *     an assigned IT Support member.
     *   - In Progress -> Completed requires a Problem Analysis AND a Resolution.
     *   - Completed is terminal (no transitions out of it).
     *   - completed_at / completed_by are recorded when the ticket is Completed.
     *
     * Returns JSON when the request is AJAX (inline table controls),
     * otherwise redirects back to the ticket.
     */
    public function updateStatus(Request $request, string $id)
    {
        $user = auth()->user();
        abort_unless($user->canManageTickets(), 403, 'Only IT Support or management can change ticket status.');

        $validated = $request->validate([
            'status' => 'required|in:Waiting Confirmation,In Progress,Completed',
            'problem_analysis' => 'nullable|string',
            'resolution' => 'nullable|string',
        ]);

        $ticket = $this->findTicketForUser($id);
        $newStatus = $validated['status'];

        // IT Support may only update the status of tickets assigned to them.
        if ($user->isStaff() && $ticket->assignee_id !== $user->id) {
            return $this->validationFailure($request, 'status', 'You can only update the status of tickets assigned to you.');
        }

        if ($ticket->status === $newStatus) {
            return $this->statusResponse($request, $ticket, $newStatus);
        }

        // Completed is terminal: tickets cannot be reopened or moved backwards.
        if ($ticket->status === 'Completed') {
            return $this->validationFailure($request, 'status', 'Completed tickets cannot be reopened.');
        }

        // Analysis/resolution may come from the request or from values already
        // stored on the ticket (e.g. previously saved while In Progress).
        $analysis = trim((string) ($request->input('problem_analysis') ?? $ticket->problem_analysis ?? ''));
        $resolution = trim((string) ($request->input('resolution') ?? $ticket->resolution ?? ''));

        if ($newStatus === 'In Progress') {
            if ($analysis === '') {
                return $this->validationFailure($request, 'problem_analysis', 'Problem analysis is required before moving the ticket to In Progress.');
            }
            if (!$ticket->assignee_id) {
                return $this->validationFailure($request, 'assignee', 'Assign the ticket to an IT Support member before starting work.');
            }
        }

        if ($newStatus === 'Completed') {
            if ($analysis === '') {
                return $this->validationFailure($request, 'problem_analysis', 'Problem analysis is required before the ticket can be Completed.');
            }
            if ($resolution === '') {
                return $this->validationFailure($request, 'resolution', 'Resolution is required before the ticket can be Completed.');
            }
        }

        $updates = ['status' => $newStatus];

        if ($newStatus === 'In Progress') {
            $updates['problem_analysis'] = $analysis;
            if (!$ticket->first_response_at) {
                $updates['first_response_at'] = now();
            }
            if (!$ticket->problem_analysis_at) {
                $updates['problem_analysis_at'] = now();
            }
        }

        if ($newStatus === 'Completed') {
            $updates['problem_analysis'] = $analysis;
            $updates['resolution'] = $resolution;
            $updates['completed_at'] = $ticket->completed_at ?? now();
            $updates['completed_by'] = $user->id;
            if (!$ticket->resolution_at) {
                $updates['resolution_at'] = now();
            }
        }

        $ticket->update($updates);

        return $this->statusResponse($request, $ticket, $newStatus);
    }

    /**
     * Assign a ticket to the current user (Assign to Me).
     *
     * IT Support (staff) may only self-assign and only when the ticket is
     * unassigned or already assigned to them. Admin/Manager may take any
     * active ticket. Taking a ticket that is not yet In Progress moves it to
     * In Progress, which requires a Problem Analysis (backend enforced).
     *
     * Returns JSON when the request is AJAX, otherwise redirects to the ticket.
     */
    public function assignToMe(Request $request, string $id)
    {
        $user = auth()->user();
        abort_unless($user->canManageTickets(), 403, 'Only IT Support or management can assign tickets to themselves.');

        $ticket = $this->findTicketForUser($id);

        // Completed tickets cannot be picked up again.
        if ($ticket->status === 'Completed') {
            return $this->validationFailure($request, 'status', 'Completed tickets cannot be reassigned.');
        }

        // IT Support may only self-assign tickets that are unassigned or
        // already theirs. Admin/Manager may take any ticket.
        if ($user->isStaff() && $ticket->assignee_id && $ticket->assignee_id !== $user->id) {
            return $this->validationFailure($request, 'assignee', 'This ticket is already assigned to another IT Support member.');
        }

        // Moving the ticket to In Progress requires a problem analysis.
        $analysis = trim((string) ($request->input('problem_analysis') ?? $ticket->problem_analysis ?? ''));
        if ($ticket->status !== 'In Progress' && $analysis === '') {
            return $this->validationFailure($request, 'problem_analysis', 'Problem analysis is required before you can take this ticket.');
        }

        $updates = [
            'assignee_id' => $user->id,
        ];

        if (!$ticket->assigned_at) {
            $updates['assigned_at'] = now();
        }

        if ($analysis !== '') {
            $updates['problem_analysis'] = $analysis;
            if (!$ticket->problem_analysis_at) {
                $updates['problem_analysis_at'] = now();
            }
        }

        if ($ticket->status !== 'In Progress') {
            $updates['status'] = 'In Progress';
            if (!$ticket->first_response_at) {
                $updates['first_response_at'] = now();
            }
        }

        $ticket->update($updates);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'assignee_id' => $user->id, 'status' => $ticket->status]);
        }

        return redirect()->route('tickets.show', $id)
            ->with('success', 'Ticket assigned to you and moved to In Progress.');
    }

    /**
     * Update ticket assignee (API).
     */
    public function updateAssignee(Request $request, string $id): JsonResponse
    {
        if (!in_array(auth()->user()->role?->slug, ['admin', 'manager'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $ticket = Ticket::findOrFail($id);
        $updates = ['assignee_id' => $validated['assignee_id']];
        if ($validated['assignee_id'] && !$ticket->assigned_at) {
            $updates['assigned_at'] = now();
        }
        $ticket->update($updates);

        return response()->json(['success' => true]);
    }

    /**
     * Update ticket priority (API).
     */
    public function updatePriority(Request $request, string $id): JsonResponse
    {
        if (!in_array(auth()->user()->role?->slug, ['admin', 'manager', 'staff'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'priority' => 'required|in:low,medium,high,critical',
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->update(['priority' => $validated['priority']]);

        return response()->json(['success' => true]);
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
        $ticket = $this->findTicketForUser($id);

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

    /**
     * Download a ticket attachment (ticket owner, assignee, or support).
     */
    public function downloadAttachment(string $ticketId, string $attachmentId): BinaryFileResponse
    {
        $ticket = $this->findTicketForUser($ticketId);
        $attachment = $ticket->attachments()->findOrFail($attachmentId);

        $path = storage_path('app/private/attachments/' . $attachment->stored_filename);
        abort_unless(is_file($path), 404, 'Attachment file not found.');

        return response()->download($path, $attachment->original_filename, [
            'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
        ]);
    }

    /**
     * Delete a ticket attachment (ticket owner, assignee, or support).
     */
    public function destroyAttachment(string $ticketId, string $attachmentId): RedirectResponse
    {
        $ticket = $this->findTicketForUser($ticketId);
        $attachment = $ticket->attachments()->findOrFail($attachmentId);

        $path = storage_path('app/private/attachments/' . $attachment->stored_filename);
        if (is_file($path)) {
            @unlink($path);
        }

        $attachment->delete();

        return redirect()->route('tickets.show', $ticketId)
            ->with('success', 'Attachment deleted successfully.');
    }

    /**
     * Update the SLA priority of a ticket (API).
     * Manager/Admin only — the SLA is a support-side field.
     *
     * Returns JSON (inline table controls) and persists to the database.
     */
    public function updateSla(Request $request, string $id): JsonResponse
    {
        if (!in_array(auth()->user()->role?->slug, ['admin', 'manager'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'sla_priority' => 'nullable|in:low,medium,high,critical',
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->update(['sla_priority' => $validated['sla_priority'] ?? null]);

        return response()->json(['success' => true, 'sla_priority' => $validated['sla_priority'] ?? null]);
    }

    /**
     * Global search across Tickets, Users and Categories (AJAX autocomplete).
     *
     * Ticket visibility follows the same rules as show(): Managers/Admins see
     * everything; everyone else only sees tickets they reported or are assigned to.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));
        $user = auth()->user();

        if (mb_strlen($q) < 2) {
            return response()->json(['tickets' => [], 'users' => [], 'categories' => []]);
        }

        $isManagement = $user->canManageTickets() && ($user->isAdmin() || $user->isManager());

        // ---- Tickets ----
        $ticketQuery = Ticket::query()
            ->with(['user', 'assignee'])
            ->where(function ($qq) use ($q) {
                $qq->where('ticket_number', 'ILIKE', "%{$q}%")
                   ->orWhere('description', 'ILIKE', "%{$q}%")
                   ->orWhereHas('user', function ($u) use ($q) {
                       $u->where('name', 'ILIKE', "%{$q}%")->orWhere('email', 'ILIKE', "%{$q}%");
                   })
                   ->orWhereHas('assignee', function ($u) use ($q) {
                       $u->where('name', 'ILIKE', "%{$q}%")->orWhere('email', 'ILIKE', "%{$q}%");
                   });
            });

        if (!$isManagement) {
            $ticketQuery->where(function ($qq) use ($user) {
                $qq->where('user_id', $user->id)->orWhere('assignee_id', $user->id);

                // IT Support may also search the unassigned queue.
                if ($user->isStaff()) {
                    $qq->orWhereNull('assignee_id');
                }
            });
        }

        $tickets = $ticketQuery->latest()->take(6)->get()->map(fn ($t) => [
            'id'      => $t->id,
            'ticket_number' => $t->ticket_number,
            'description' => \Illuminate\Support\Str::limit($t->description ?? '', 90),
            'status'  => $t->status,
            'priority'=> $t->priority,
            'requester' => $t->user?->name,
            'assignee'  => $t->assignee?->name,
            'url'     => route('tickets.show', $t->id),
        ])->values();

        // ---- Users (management only) ----
        $users = collect();
        if ($isManagement) {
            $users = User::with('role')
                ->where(function ($u) use ($q) {
                    $u->where('name', 'ILIKE', "%{$q}%")->orWhere('email', 'ILIKE', "%{$q}%");
                })
                ->orderBy('name')
                ->take(5)
                ->get()
                ->map(fn ($u) => [
                    'id'    => $u->id,
                    'name'  => $u->name,
                    'email' => $u->email,
                    'role'  => $u->role?->name,
                    'url'   => $user->isAdmin() ? route('users.show', $u->id) : null,
                ])
                ->values();
        }

        // ---- Categories ----
        $categories = Category::where('is_active', true)
            ->where('name', 'ILIKE', "%{$q}%")
            ->orderBy('name')
            ->take(5)
            ->get()
            ->map(fn ($c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'url'  => route('categories.index'),
            ])->values();

        return response()->json([
            'tickets'    => $tickets->all(),
            'users'      => $users->all(),
            'categories' => $categories->all(),
        ]);
    }

    /**
     * Build the success response for a status change (JSON or redirect).
     */
    private function statusResponse(Request $request, Ticket $ticket, string $newStatus)
    {
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $newStatus]);
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Build the failure response for a workflow rule (JSON or redirect back
     * with a validation error).
     */
    private function validationFailure(Request $request, string $field, string $message)
    {
        if ($request->wantsJson()) {
            return response()->json(['error' => $message, 'errors' => [$field => $message]], 422);
        }

        return redirect()->back()->withErrors([$field => $message])->withInput();
    }

    /**
     * Users eligible to be assigned tickets (Admin/Manager/Staff).
     */
    private function supportUsers()
    {
        return User::where('is_active', true)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['admin', 'manager', 'staff']);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Locate a ticket the current user may view or update.
     *
     * Admin/Manager can access every ticket; everyone else is limited to
     * tickets they reported or are assigned to.
     */
    private function findTicketForUser(string $id): Ticket
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isManager()) {
            $ticket = Ticket::find($id);
        } else {
            // Wrap the user/assignee conditions in a nested group so the
            // generated SQL is "(user_id = ? or assignee_id = ?) and id = ?".
            // Without the group, SQL precedence makes "or" bind looser than the
            // trailing "and id = ?", causing the id constraint to be ignored.
            // IT Support (staff) may additionally open unassigned tickets so
            // they can pick them up with "Assign to Me".
            $ticket = Ticket::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('assignee_id', $user->id);

                if ($user->isStaff()) {
                    $q->orWhereNull('assignee_id');
                }
            })->find($id);
        }

        abort_unless($ticket, 404, 'Ticket not found or you do not have access to it.');

        return $ticket;
    }

    /**
      * Resolve the SLA priority level for a new ticket.
      *
      * Precedence: explicit policy selection -> category SLA policy ->
      * active policy matching the ticket priority.
      */
    private function resolveSlaPriority(?int $explicitPolicyId, ?Category $category, string $priority): ?string
    {
        $policy = $this->resolveSlaPolicyModel($explicitPolicyId, $category, $priority);

        return $policy?->priority;
    }

    /**
     * Resolve the SLA policy model for a new ticket.
     */
    private function resolveSlaPolicyModel(?int $explicitPolicyId, ?Category $category, string $priority): ?SlaPolicy
    {
        if ($explicitPolicyId) {
            return SlaPolicy::find($explicitPolicyId);
        }

        if ($category && $category->sla_policy_id) {
            return $category->slaPolicy;
        }

        return SlaPolicy::where('is_active', true)->where('priority', $priority)->first();
    }
}