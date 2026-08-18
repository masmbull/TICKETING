<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketAttachment;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\SlaMapping;
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

        // Support users explicitly set priority. Regular users never see the
        // priority field — their ticket gets NO priority and NO SLA until a
        // manager/admin assigns one.
        $priority = $validated['priority'] ?? null;
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

        $ticketData = [
            'ticket_number'   => $ticketNumber,
            'description'     => $validated['description'],
            'status'          => 'Waiting Confirmation',
            'user_id'         => auth()->id(),
            'category_id'     => $validated['category_id'] ?? null,
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'assignee_id'     => $assigneeId,
        ];

        $ticket = Ticket::create($ticketData);

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

        return redirect()->route('tickets.create')
            ->with('ticket_created', $ticket->ticket_number);
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
            'status' => 'required|in:In Progress,Waiting Confirmation,Completed',
            'problem_analysis' => 'nullable|string',
            'resolution' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $ticket = $this->findTicketForUser($id);
        $newStatus = $validated['status'];

        // Admin/Manager can update the assignee alongside status.
        if (in_array($user->role?->slug, ['admin', 'manager']) && array_key_exists('assignee_id', $validated)) {
            $ticket->update(['assignee_id' => $validated['assignee_id'] ?: null]);
        }

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

    public function takeTicket(Request $request, string $id)
    {
        $user = auth()->user();
        abort_unless($user->canManageTickets(), 403);

        $ticket = $this->findTicketForUser($id);

        if ($ticket->status === 'Completed') {
            return $this->validationFailure($request, 'status', 'Completed tickets cannot be taken.');
        }

        if ($user->isStaff() && $ticket->assignee_id && $ticket->assignee_id !== $user->id) {
            return $this->validationFailure($request, 'assignee', 'This ticket is already assigned to another support member.');
        }

        $ticket->update([
            'assignee_id' => $user->id,
            'assigned_at' => $ticket->assigned_at ?? now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'assignee_id' => $user->id]);
        }

        return redirect()->route('tickets.show', $id)
            ->with('success', 'Ticket taken successfully.');
    }

    public function submitAnalysis(Request $request, string $id)
    {
        $user = auth()->user();
        abort_unless($user->canManageTickets(), 403);

        $ticket = $this->findTicketForUser($id);

        if ($user->isStaff() && $ticket->assignee_id !== $user->id) {
            return $this->validationFailure($request, 'status', 'You can only process your own assigned tickets.');
        }

        if ($ticket->status !== 'Waiting Confirmation') {
            return $this->validationFailure($request, 'status', 'Only tickets in Waiting Confirmation can be processed.');
        }

        $validated = $request->validate([
            'problem_analysis' => 'required|string|min:3',
        ]);

        $ticket->update([
            'status' => 'In Progress',
            'problem_analysis' => $validated['problem_analysis'],
            'problem_analysis_at' => $ticket->problem_analysis_at ?? now(),
            'first_response_at' => $ticket->first_response_at ?? now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => 'In Progress']);
        }

        return redirect()->route('tickets.show', $id)
            ->with('success', 'Analysis submitted. Ticket is now In Progress.');
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

    public function completeTicket(Request $request, string $id)
    {
        $user = auth()->user();
        abort_unless($user->canManageTickets(), 403);

        $ticket = $this->findTicketForUser($id);

        if ($user->isStaff() && $ticket->assignee_id !== $user->id) {
            return $this->validationFailure($request, 'status', 'You can only complete your own assigned tickets.');
        }

        if ($ticket->status !== 'In Progress') {
            return $this->validationFailure($request, 'status', 'Only In Progress tickets can be completed.');
        }

        $validated = $request->validate([
            'resolution' => 'required|string|min:3',
        ]);

        $ticket->update([
            'status' => 'Completed',
            'resolution' => $validated['resolution'],
            'completed_at' => $ticket->completed_at ?? now(),
            'completed_by' => $ticket->completed_by ?? $user->id,
            'resolution_at' => $ticket->resolution_at ?? now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => 'Completed']);
        }

        return redirect()->route('tickets.show', $id)
            ->with('success', 'Ticket completed successfully.');
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

        // Auto-resolve SLA from Category+Subcategory mapping
        if ($validated['assignee_id'] && !$ticket->sla_priority) {
            $mapping = SlaMapping::where('category_id', $ticket->category_id)
                ->where(function ($q) use ($ticket) {
                    $q->where('sub_category_id', $ticket->sub_category_id)
                      ->orWhereNull('sub_category_id');
                })
                ->where('is_active', true)
                ->first();

            if ($mapping) {
                $policy = \App\Models\SlaPolicy::where('priority', $mapping->priority)
                    ->where('is_active', true)
                    ->first();

                if ($policy && $policy->resolution_days) {
                    $startedAt = now();
                    $ticket->update([
                        'priority' => $mapping->priority,
                        'sla_priority' => $mapping->priority,
                        'sla_started_at' => $startedAt,
                        'sla_deadline' => $startedAt->copy()->addDays($policy->resolution_days),
                    ]);
                }
            }
        }

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
     * When a valid SLA priority is given, finds the matching active policy,
     * assigns it, and calculates the deadline from sla_started_at + resolution_hours.
     * When null/cleared, removes the SLA assignment.
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
        $newPriority = $validated['sla_priority'] ?? null;

        if ($newPriority) {
            $policy = SlaPolicy::where('priority', $newPriority)->where('is_active', true)->first();
            if (!$policy) {
                return response()->json([
                    'error' => 'No active SLA policy found for ' . $newPriority . ' priority.',
                ], 422);
            }

            $startedAt = $ticket->sla_started_at ?? now();
            $ticket->update([
                'priority'       => $newPriority,
                'sla_priority'   => $newPriority,
                'sla_started_at' => $startedAt,
                'sla_deadline'   => $startedAt->copy()->addDays($policy->resolution_days ?? ceil($policy->resolution_hours / 24)),
            ]);
        } else {
            $ticket->update([
                'sla_priority'   => null,
                'sla_started_at' => null,
                'sla_deadline'   => null,
            ]);
        }

        return response()->json([
            'success'       => true,
            'sla_priority'  => $ticket->sla_priority,
            'sla_deadline'  => $ticket->sla_deadline?->format('d M Y, H:i'),
            'priority'      => $ticket->priority,
        ]);
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
    private function resolveSlaPriority(?int $explicitPolicyId, ?Category $category, ?string $priority): ?string
    {
        $policy = $this->resolveSlaPolicyModel($explicitPolicyId, $category, $priority);

        return $policy?->priority;
    }

    /**
     * Resolve the SLA policy model for a new ticket.
     */
    private function resolveSlaPolicyModel(?int $explicitPolicyId, ?Category $category, ?string $priority): ?SlaPolicy
    {
        if ($explicitPolicyId) {
            return SlaPolicy::find($explicitPolicyId);
        }

        if ($category && $category->sla_policy_id) {
            return $category->slaPolicy;
        }

        if (!$priority) {
            return null;
        }

        return SlaPolicy::where('is_active', true)->where('priority', $priority)->first();
    }
}