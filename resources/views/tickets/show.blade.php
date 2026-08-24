@extends('layouts.app')

@section('title', $ticket->ticket_number . ' - MITO IT Helpdesk')

@php
    $canManage = Auth::user()->canManageTickets();
    $isCompleted = $ticket->status === 'Completed';
    $isWaitingConfirmation = $ticket->status === 'Waiting Confirmation';
    $isInProgress = $ticket->status === 'In Progress';
    $isStaff = auth()->user()->isStaff();
    $isManager = auth()->user()->isAdmin() || auth()->user()->isManager();
    $isMyTicket = $ticket->assignee_id == auth()->id();
    $isUnassigned = is_null($ticket->assignee_id);

    $statusColors = [
        'Waiting Confirmation' => 'bg-amber-500/10 text-amber-600 border-amber-200 dark:border-amber-900/30',
        'In Progress' => 'bg-blue-500/10 text-blue-600 border-blue-200 dark:border-blue-900/30',
        'Completed' => 'bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:border-emerald-900/30',
    ];
    $priorityColors = [
        'low' => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-700/50 dark:border-slate-600',
        'medium' => 'bg-blue-500/10 text-blue-600 border-blue-200 dark:border-blue-900/30',
        'high' => 'bg-orange-500/10 text-orange-600 border-orange-200 dark:border-orange-900/30',
        'critical' => 'bg-red-500/10 text-red-600 border-red-200 dark:border-red-900/30',
    ];
    $slaColors = $priorityColors;
    $validPriorities = ['low', 'medium', 'high', 'critical'];
    $hasSla = !is_null($ticket->sla_deadline) && !is_null($ticket->sla_started_at) && in_array($ticket->sla_priority, $validPriorities);

    // Single source of truth: the model assembles and chronologically sorts
    // lifecycle events (including reopen cycles replayed from audit logs).
    $timeline = $ticket->timeline;

    // Presentation-only maps for the timeline UI (icon paths + semantic
    // marker colors per event type). Data itself comes from $ticket->timeline.
    $timelineIcons = [
        'Ticket Created'   => 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z',
        'Assigned'         => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        'In Progress'      => 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z',
        'Problem Analysis' => 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',
        'Resolution'       => 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z',
        'Re-opened'        => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99',
        'Completed'        => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ];
    $timelineMarkers = [
        'Ticket Created'   => 'bg-[#E30613]/10 text-[#E30613]',
        'Assigned'         => 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
        'In Progress'      => 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
        'Problem Analysis' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
        'Resolution'       => 'bg-violet-500/10 text-violet-600 dark:text-violet-400',
        'Re-opened'        => 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
        'Completed'        => 'bg-emerald-500 text-white shadow-sm shadow-emerald-500/40',
    ];

    $metaParts = array_filter([
        $ticket->category->name ?? 'Uncategorized',
        $ticket->subCategory->name ?? null,
        $ticket->user->name ?? null,
    ]);
@endphp

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route($isManager ? 'tickets.all' : 'tickets.index') }}"
               class="shrink-0 p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white break-all">{{ $ticket->ticket_number }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ implode(' · ', $metaParts) }}</p>
            </div>
        </div>
        <span class="px-3 py-1.5 rounded-lg text-sm font-medium border shrink-0 {{ $statusColors[$ticket->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">{{ $ticket->status }}</span>
    </div>

    @if($isCompleted)
    <div class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/30 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-emerald-900 dark:text-emerald-100">Ticket Closed / Completed</h3>
                <p class="text-sm text-emerald-800 dark:text-emerald-200 mt-1">
                    @if($ticket->completed_by)
                        Completed by <strong>{{ $ticket->completedBy?->name }}</strong>
                    @endif
                    @if($ticket->completed_at)
                        on <strong>{{ $ticket->completed_at->format('d M Y, H:i') }}</strong>
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 items-start">
        {{-- LEFT COLUMN --}}
        <div class="xl:col-span-2 space-y-4">
            {{-- Description --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Description</h2>
                </div>
                <div class="p-4">
                    <p class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-wrap break-words">{{ $ticket->description }}</p>
                    @if($ticket->attachments->count() > 0)
                    <div x-data="{ lightboxImg: '', lightboxOpen: false, pdfSrc: '', pdfOpen: false }">
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($ticket->attachments as $attachment)
                                @php
                                    $ext = strtolower(pathinfo($attachment->original_filename, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['png','jpg','jpeg','gif','webp']);
                                    $isPdf = $ext === 'pdf';
                                @endphp
                                @if($isImage)
                                <div class="group relative w-20 h-20 rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden bg-slate-100 dark:bg-slate-700 cursor-pointer hover:border-[#E30613] transition-colors"
                                     @click="lightboxImg = '{{ route('tickets.attachments.serve', [$ticket->id, $attachment->id]) }}'; lightboxOpen = true">
                                    <img src="{{ route('tickets.attachments.serve', [$ticket->id, $attachment->id]) }}"
                                         alt="{{ $attachment->original_filename }}"
                                         class="w-full h-full object-cover">
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent px-1 py-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <p class="text-[10px] text-white truncate">{{ $attachment->original_filename }}</p>
                                    </div>
                                </div>
                                @elseif($isPdf)
                                <div class="group relative w-20 h-20 rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden bg-red-50 dark:bg-red-900/20 cursor-pointer hover:border-[#E30613] transition-colors flex flex-col items-center justify-center"
                                     @click="pdfSrc = '{{ route('tickets.attachments.serve', [$ticket->id, $attachment->id]) }}'; pdfOpen = true">
                                    <svg class="w-7 h-7 text-red-500 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <span class="text-[9px] font-medium text-red-600 dark:text-red-400 uppercase">PDF</span>
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/50 to-transparent px-1 py-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <p class="text-[10px] text-white truncate">{{ $attachment->original_filename }}</p>
                                    </div>
                                </div>
                                @else
                                <a href="{{ route('tickets.attachments.download', [$ticket->id, $attachment->id]) }}"
                                   class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-xs text-slate-600 dark:text-slate-300 hover:border-[#E30613] transition-colors">
                                    <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    <span class="truncate max-w-[120px]">{{ $attachment->original_filename }}</span>
                                </a>
                                @endif
                            @endforeach
                        </div>
                        {{-- Image Lightbox --}}
                        <template x-if="lightboxOpen">
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" @click.self="lightboxOpen = false" @keydown.escape.window="lightboxOpen = false">
                                <div class="relative max-w-4xl max-h-[90vh]">
                                    <img :src="lightboxImg" class="max-h-[85vh] max-w-full rounded-lg shadow-2xl object-contain">
                                    <button @click="lightboxOpen = false" class="absolute -top-3 -right-3 w-8 h-8 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center text-slate-600 hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        {{-- PDF Preview Modal --}}
                        <template x-if="pdfOpen">
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm sm:p-3 md:p-4" @keydown.escape.window="pdfOpen = false">
                                <div class="relative w-full h-full sm:w-[95vw] sm:h-[92vh] md:w-[90vw] md:h-[90vh] lg:w-[85vw] lg:h-[90vh] bg-white dark:bg-slate-800 sm:rounded-xl shadow-2xl overflow-hidden flex flex-col">
                                    <div class="flex items-center justify-between px-3 py-2 sm:px-4 sm:py-2.5 border-b border-slate-200 dark:border-slate-700 shrink-0">
                                        <span class="text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-300 truncate">PDF Preview</span>
                                        <div class="flex items-center gap-2">
                                            <a :href="pdfSrc" target="_blank" class="text-xs text-[#E30613] hover:underline font-medium hidden sm:inline">Open in new tab</a>
                                            <button @click="pdfOpen = false" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-500 hover:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <iframe :src="pdfSrc" class="flex-1 w-full border-0" frameborder="0"></iframe>
                                </div>
                            </div>
                        </template>
                    </div>
                    @endif
                </div>
            </div>

            {{-- WORKFLOW CARD --}}
            @if($canManage)
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Workflow</h2>
                </div>
                <div class="p-4 space-y-4">
                    {{-- Status indicator --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Status</label>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $isWaitingConfirmation ? 'bg-amber-500' : ($isInProgress ? 'bg-blue-500' : 'bg-emerald-500') }}"></span>
                            <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $ticket->status }}</span>
                        </div>
                    </div>

                    {{-- WAITING CONFIRMATION + UNASSIGNED → Take Ticket --}}
                    @if($isWaitingConfirmation && $isUnassigned)
                    <div class="bg-amber-50 dark:bg-amber-500/10 rounded-lg p-4 border border-amber-200 dark:border-amber-900/30">
                        <p class="text-sm text-amber-700 dark:text-amber-300 mb-3">This ticket is unassigned and waiting for a technician to pick it up.</p>
                        <div x-data="{ taking: false }">
                            <form method="POST" action="{{ route('tickets.take', $ticket->id) }}" @submit="taking = true">
                                @csrf
                                <button type="submit" :disabled="taking"
                                        class="w-full px-4 py-2.5 bg-[#E30613] hover:bg-[#c4050f] text-white text-sm font-semibold rounded-lg transition-all duration-200 hover:brightness-110 active:scale-[0.98] disabled:opacity-60 flex items-center justify-center gap-2">
                                    <svg x-show="!taking" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                    <span x-show="!taking">Take Ticket</span>
                                    <svg x-show="taking" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M12 2v6"/></svg>
                                    <span x-show="taking">Taking…</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- MANAGER: OPEN/UNASSIGNED → Assign --}}
                    @if($isWaitingConfirmation && $isUnassigned && $isManager)
                    <div x-data="{
                        assigneeId: '',
                        saving: false,
                        assign() {
                            this.saving = true;
                            fetch('/tickets/{{ $ticket->id }}/assign', {
                                method: 'PATCH',
                                credentials: 'same-origin',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                                body: JSON.stringify({ assignee_id: this.assigneeId || null })
                            })
                            .then(r => { if (!r.ok) return r.json().then(e => Promise.reject(e)); return r.json(); })
                            .then(() => { this.saving = false; window.location.reload(); })
                            .catch(e => { this.saving = false; window.MITO.toast(e.error || 'Failed to assign.', 'error'); });
                        }
                    }">
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Assign to</label>
                        <div class="flex items-center gap-2">
                            <select x-model="assigneeId" class="flex-1 px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                                <option value="">Select staff…</option>
                                @foreach($staffUsers ?? [] as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                            <button @click="assign()" :disabled="saving || !assigneeId"
                                    class="px-3 py-1.5 text-xs font-medium text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-all disabled:opacity-50 flex items-center gap-1">
                                <span x-show="!saving">Assign</span>
                                <svg x-show="saving" class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M12 2v6"/></svg>
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- WAITING CONFIRMATION + ASSIGNED TO ME → Submit Analysis --}}
                    @if($isWaitingConfirmation && $isMyTicket)
                    <form method="POST" action="{{ route('tickets.submit-analysis', $ticket->id) }}" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Problem Analysis</label>
                            <textarea name="problem_analysis" rows="3" required
                                      class="w-full px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 resize-y"
                                      placeholder="Enter your analysis/diagnosis before starting work…">{{ old('problem_analysis', $ticket->problem_analysis) }}</textarea>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Enter your analysis/diagnosis before starting work.</p>
                            @error('problem_analysis')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <button type="submit" :disabled="submitting"
                                class="w-full px-4 py-2.5 bg-[#E30613] hover:bg-[#c4050f] text-white text-sm font-semibold rounded-lg transition-all duration-200 hover:brightness-110 active:scale-[0.98] disabled:opacity-60 flex items-center justify-center gap-2">
                            <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-show="!submitting">Submit Analysis</span>
                            <svg x-show="submitting" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M12 2v6"/></svg>
                            <span x-show="submitting">Submitting…</span>
                        </button>
                    </form>
                    @endif

                    {{-- IN PROGRESS + ASSIGNED TO ME → Complete Ticket --}}
                    @if($isInProgress && $isMyTicket)
                    <div class="space-y-3">
                        {{-- Problem Analysis stays editable while In Progress;
                             saving it must not change the status. --}}
                        <div x-data="{ savingAnalysis: false }">
                            <form method="POST" action="{{ route('tickets.status.update', $ticket->id) }}" @submit="savingAnalysis = true">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="In Progress">
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Problem Analysis</label>
                                <textarea name="problem_analysis" rows="3"
                                          class="w-full px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 resize-y"
                                          placeholder="Describe your analysis/diagnosis…">{{ old('problem_analysis', $ticket->problem_analysis) }}</textarea>
                                @error('problem_analysis')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
                                <button type="submit" :disabled="savingAnalysis"
                                        class="w-full mt-2 px-3 py-1.5 text-xs font-medium text-white bg-slate-600 hover:bg-slate-500 rounded-lg transition-all disabled:opacity-50 flex items-center justify-center gap-1">
                                    <span x-show="!savingAnalysis">Save Analysis</span>
                                    <span x-show="savingAnalysis">Saving…</span>
                                </button>
                            </form>
                        </div>
                        <form method="POST" action="{{ route('tickets.complete', $ticket->id) }}" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Resolution / Problem Solving</label>
                                <textarea name="resolution" rows="3" required
                                          class="w-full px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 resize-y"
                                          placeholder="How was the issue resolved…">{{ old('resolution', $ticket->resolution) }}</textarea>
                                @error('resolution')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <button type="submit" :disabled="submitting"
                                    class="w-full mt-3 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-lg transition-all duration-200 hover:brightness-110 active:scale-[0.98] disabled:opacity-60 flex items-center justify-center gap-2">
                                <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!submitting">Complete Ticket</span>
                                <svg x-show="submitting" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M12 2v6"/></svg>
                                <span x-show="submitting">Completing…</span>
                            </button>
                        </form>
                    </div>
                    @endif

                    {{-- COMPLETED → Re-open Ticket (no email, SLA keeps running) --}}
                    @if($isCompleted)
                    <div class="bg-amber-50 dark:bg-amber-500/10 rounded-lg p-4 border border-amber-200 dark:border-amber-900/30">
                        <p class="text-sm text-amber-700 dark:text-amber-300 mb-3">This ticket is completed. Re-opening returns it to In Progress so the resolution can be updated. The SLA clock is not reset and no email is sent.</p>
                        <div x-data="{ reopening: false }">
                            <form method="POST" action="{{ route('tickets.reopen', $ticket->id) }}" @submit="reopening = true">
                                @csrf
                                <button type="submit" :disabled="reopening"
                                        class="w-full px-4 py-2.5 bg-[#E30613] hover:bg-[#c4050f] text-white text-sm font-semibold rounded-lg transition-all duration-200 hover:brightness-110 active:scale-[0.98] disabled:opacity-60 flex items-center justify-center gap-2">
                                    <svg x-show="!reopening" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span x-show="!reopening">Re-open Ticket</span>
                                    <svg x-show="reopening" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M12 2v6"/></svg>
                                    <span x-show="reopening">Re-opening…</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- MANAGER CONTROLS (all non-completed states) --}}
                    @if($isManager && !$isCompleted)
                    <div class="border-t border-slate-200 dark:border-slate-700 pt-4 space-y-3">
                        {{-- Reassign --}}
                        @if($ticket->assignee_id)
                        <div x-data="{
                            assigneeId: '{{ $ticket->assignee_id }}',
                            saving: false,
                            reassign() {
                                this.saving = true;
                                fetch('/tickets/{{ $ticket->id }}/assign', {
                                    method: 'PATCH',
                                    credentials: 'same-origin',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ assignee_id: this.assigneeId || null })
                                })
                                .then(r => { if (!r.ok) return r.json().then(e => Promise.reject(e)); return r.json(); })
                                .then(() => { this.saving = false; window.location.reload(); })
                                .catch(e => { this.saving = false; window.MITO.toast(e.error || 'Failed.', 'error'); });
                            }
                        }">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Assignee</label>
                            <div class="flex items-center gap-2">
                                <select x-model="assigneeId" class="flex-1 px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                                    <option value="">Unassigned</option>
                                    @foreach($staffUsers ?? [] as $staff)
                                    <option value="{{ $staff->id }}" {{ $ticket->assignee_id == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" @click="reassign()" :disabled="saving"
                                        class="px-3 py-1.5 text-xs font-medium text-white bg-slate-600 hover:bg-slate-500 rounded-lg transition-all disabled:opacity-50 flex items-center gap-1">
                                    <span x-show="!saving">Save</span>
                                    <svg x-show="saving" class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M12 2v6"/></svg>
                                </button>
                            </div>
                        </div>
                        @endif

                        {{-- SLA --}}
                        <div x-data="{
                            slaPriority: '{{ $ticket->sla_priority ?? '' }}',
                            saving: false,
                            updateSla() {
                                this.saving = true;
                                fetch('/tickets/{{ $ticket->id }}/sla', {
                                    method: 'PATCH',
                                    credentials: 'same-origin',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ sla_priority: this.slaPriority || null })
                                })
                                .then(r => { if (!r.ok) return r.json().then(e => Promise.reject(e)); return r.json(); })
                                .then(d => { this.saving = false; window.MITO.toast('SLA updated.', 'success'); })
                                .catch(e => { this.saving = false; window.MITO.toast(e.error || 'Failed.', 'error'); });
                            }
                        }">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">SLA Priority</label>
                            <div class="flex items-center gap-2">
                                <select x-model="slaPriority" @change="updateSla()" class="flex-1 px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                                    <option value="">No SLA</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                                <svg x-show="saving" class="w-4 h-4 animate-spin text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M12 2v6"/></svg>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Resolution Details (In Progress or Completed) --}}
            @if(($isInProgress || $isCompleted) && $ticket->problem_analysis)
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700 flex items-center gap-2">
                    @if($isCompleted)
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Resolution Details</h2>
                </div>
                <div class="p-4">
                    @if($isCompleted)
                    <div class="mb-4 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-900/30">
                        <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">Ticket Closed / Completed</p>
                    </div>
                    @endif
                    <div class="space-y-4">
                    {{-- Problem Analysis --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Problem Analysis</label>
                        <p class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 whitespace-pre-wrap break-words">{{ $ticket->problem_analysis ?? '—' }}</p>
                    </div>

                    {{-- Resolution --}}
                    @if($ticket->resolution)
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Resolving / Resolution</label>
                        <p class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 whitespace-pre-wrap break-words">{{ $ticket->resolution ?? '—' }}</p>
                    </div>
                    @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Comments --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Comments ({{ $ticket->comments->count() }})</h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($ticket->comments as $comment)
                    <div class="p-3">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-lg bg-[#E30613] flex items-center justify-center text-white font-bold text-xs flex-shrink-0">{{ strtoupper(substr($comment->user->name ?? '?', 0, 2)) }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $comment->user->name ?? '-' }}</span>
                                    <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-slate-600 dark:text-slate-300 break-words">
                                    @php
                                        // Escape and highlight mentions
                                        $displayText = e($comment->comment);

                                        // 1) Mentions from comment_mentions table (new comments)
                                        $mentions = $comment->mentions()->pluck('mentioned_name')->unique();

                                        // 2) Also parse the raw comment text for @Name patterns matching
                                        //    active users. This highlights mentions in comments that were
                                        //    created before the mention feature was introduced.
                                        //    We check each known user name directly (longest first) to avoid
                                        //    issues with the greedy regex grabbing too many words.
                                        $sortedNames = collect($mentionableUserNames)->sortByDesc(fn ($n) => strlen($n));
                                        foreach ($sortedNames as $actualName) {
                                            // Look for @Name anywhere in the comment (case-insensitive)
                                            $escapedSearchName = preg_quote($actualName, '/');
                                            if (preg_match('/@' . $escapedSearchName . '(?=\s|[.!?;,]|$)/i', $comment->comment)) {
                                                $mentions = $mentions->push($actualName);
                                            }
                                        }

                                        $mentions = $mentions->unique();
                                        foreach ($mentions as $mentionedName) {
                                            $escapedName = htmlspecialchars($mentionedName, ENT_QUOTES);
                                            // Match @name followed by space, punctuation, or end of string
                                            // (no word boundary for names with spaces)
                                            $pattern = '/@' . preg_quote($escapedName) . '(?=\s|[.!?;,]|$)/';
                                            $replacement = '<span class="bg-[#E30613]/10 dark:bg-[#E30613]/20 text-[#E30613] dark:text-[#E30613] font-medium px-1 rounded inline-block">@' . $escapedName . '</span>';
                                            $displayText = preg_replace($pattern, $replacement, $displayText);
                                        }
                                    @endphp
                                    {!! $displayText !!}
                                </p>
                                @if($comment->attachments && $comment->attachments->count() > 0)
                                <div class="mt-2 flex flex-wrap gap-1.5" x-data="{ lbImg: '', lbOpen: false, cPdfSrc: '', cPdfOpen: false }">
                                    @foreach($comment->attachments as $attachment)
                                        @php
                                            $cExt = strtolower(pathinfo($attachment->original_filename, PATHINFO_EXTENSION));
                                            $cIsImage = in_array($cExt, ['png','jpg','jpeg','gif','webp']);
                                            $cIsPdf = $cExt === 'pdf';
                                        @endphp
                                        @if($cIsImage)
                                        <div class="relative w-14 h-14 rounded-md border border-slate-200 dark:border-slate-600 overflow-hidden bg-slate-100 dark:bg-slate-700 cursor-pointer hover:border-[#E30613] transition-colors"
                                             @click="lbImg = '{{ route('tickets.attachments.serve', [$ticket->id, $attachment->id]) }}'; lbOpen = true">
                                            <img src="{{ route('tickets.attachments.serve', [$ticket->id, $attachment->id]) }}" alt="{{ $attachment->original_filename }}" class="w-full h-full object-cover">
                                        </div>
                                        @elseif($cIsPdf)
                                        <div class="relative w-14 h-14 rounded-md border border-slate-200 dark:border-slate-600 overflow-hidden bg-red-50 dark:bg-red-900/20 cursor-pointer hover:border-[#E30613] transition-colors flex flex-col items-center justify-center"
                                             @click="cPdfSrc = '{{ route('tickets.attachments.serve', [$ticket->id, $attachment->id]) }}'; cPdfOpen = true">
                                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            <span class="text-[8px] font-medium text-red-600 dark:text-red-400 uppercase">PDF</span>
                                        </div>
                                        @else
                                        <a href="{{ route('tickets.attachments.download', [$ticket->id, $attachment->id]) }}"
                                           class="inline-flex items-center gap-1 px-2 py-1 rounded-md border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-[11px] text-slate-600 dark:text-slate-300 hover:border-[#E30613] transition-colors">
                                            <svg class="w-3 h-3 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                            <span class="truncate max-w-[100px]">{{ $attachment->original_filename }}</span>
                                        </a>
                                        @endif
                                    @endforeach
                                    <template x-if="lbOpen">
                                        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" @click.self="lbOpen = false" @keydown.escape.window="lbOpen = false">
                                            <div class="relative max-w-4xl max-h-[90vh]">
                                                <img :src="lbImg" class="max-h-[85vh] max-w-full rounded-lg shadow-2xl object-contain">
                                                <button @click="lbOpen = false" class="absolute -top-3 -right-3 w-8 h-8 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center text-slate-600 hover:text-red-500 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="cPdfOpen">
                                        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm sm:p-3 md:p-4" @keydown.escape.window="cPdfOpen = false">
                                            <div class="relative w-full h-full sm:w-[95vw] sm:h-[92vh] md:w-[90vw] md:h-[90vh] lg:w-[85vw] lg:h-[90vh] bg-white dark:bg-slate-800 sm:rounded-xl shadow-2xl overflow-hidden flex flex-col">
                                                <div class="flex items-center justify-between px-3 py-2 sm:px-4 sm:py-2.5 border-b border-slate-200 dark:border-slate-700 shrink-0">
                                                    <span class="text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-300 truncate">PDF Preview</span>
                                                    <div class="flex items-center gap-2">
                                                        <a :href="cPdfSrc" target="_blank" class="text-xs text-[#E30613] hover:underline font-medium hidden sm:inline">Open in new tab</a>
                                                        <button @click="cPdfOpen = false" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-500 hover:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <iframe :src="cPdfSrc" class="flex-1 w-full border-0" frameborder="0"></iframe>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center"><p class="text-sm text-slate-400">No comments yet</p></div>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('tickets.comments.store', $ticket->id) }}" enctype="multipart/form-data" class="p-4 border-t border-slate-200 dark:border-slate-700 relative"
                      x-data="commentForm()" @submit="posting = true" @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false" @drop.prevent="handleDrop($event)">
                    @csrf

                    <div class="relative" x-ref="commentWrapper">
                        <!-- Contenteditable editor with mention token support -->
                        <div x-ref="commentEditor"
                             contenteditable="true"
                             role="textbox"
                             @input="syncEditorContent()"
                             @keyup="handleEditorKeyup($event)"
                             @keydown="handleEditorKeydown($event)"
                             @paste="handlePaste($event)"
                             @blur="updatePlaceholder()"
                             @focus="updatePlaceholder()"
                             class="w-full px-3 py-2.5 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white resize-y min-h-[100px] max-h-96 overflow-y-auto break-words whitespace-pre-wrap"
                             style="outline: none; word-wrap: break-word; white-space: pre-wrap; position: relative;"></div>
                        
                        <!-- Placeholder overlay (only shows when empty) -->
                        <div x-show="editorEmpty && !editorFocused"
                             class="absolute top-0 left-0 px-3 py-2.5 text-sm text-slate-400 dark:text-slate-500 pointer-events-none">
                            Type a comment or drop a file here... (use @name to mention users)
                        </div>
                        
                        <!-- Hidden input to store plain text for form submission -->
                        <input type="hidden" name="comment" x-ref="commentInput" x-model="plainTextComment" />
                        
                        <!-- Mention Autocomplete Dropdown -->
                        <div x-show="showMentionDropdown" 
                             class="absolute z-50 mt-1 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg shadow-lg w-64 max-h-64 overflow-y-auto"
                             :style="`top: ${dropdownTop}px; left: ${dropdownLeft}px;`"
                             @click.outside="showMentionDropdown = false">
                            <template x-if="mentionResults.length === 0">
                                <div class="px-3 py-2 text-sm text-slate-500 dark:text-slate-400">No users found</div>
                            </template>
                            <template x-for="(user, index) in mentionResults" :key="user.id">
                                <div @click="selectMention(user)"
                                     :class="{
                                         'bg-blue-100 dark:bg-blue-900': index === selectedMentionIndex,
                                         'hover:bg-slate-100 dark:hover:bg-slate-600': index !== selectedMentionIndex
                                     }"
                                     class="px-3 py-2.5 cursor-pointer border-b border-slate-100 dark:border-slate-600 last:border-b-0">
                                    <div class="font-medium text-sm text-slate-900 dark:text-white" x-text="user.name"></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400" x-text="user.email"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    @error('comment')<span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>@enderror

                    {{-- File input (invisible — keeps files for form submission) --}}
                    <input type="file" name="attachments[]" multiple accept=".png,.jpg,.jpeg,.pdf,.zip,image/png,image/jpeg,application/pdf,application/zip"
                           class="absolute opacity-0 w-0 h-0 pointer-events-none"
                           x-ref="fileInput"
                           @change="syncFiles()">

                    {{-- Preview attached files --}}
                    <template x-if="fileNames.length > 0">
                        <div class="mt-3 space-y-2">
                            <template x-for="(name, idx) in fileNames" :key="idx">
                                <div class="flex items-center gap-3 px-3 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600">
                                    <div class="w-8 h-8 rounded bg-[#E30613]/10 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-[#E30613]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    </div>
                                    <span class="text-sm text-slate-700 dark:text-slate-300 truncate flex-1" x-text="name"></span>
                                    <button type="button" @click="removeFile(idx)" class="text-slate-400 hover:text-red-500 transition-colors flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Dropzone area --}}
                    <div class="mt-3 relative border-2 border-dashed rounded-xl p-6 text-center transition-colors cursor-pointer"
                         :class="dragOver ? 'border-[#E30613] bg-[#E30613]/5' : 'border-slate-300 dark:border-slate-600 hover:border-slate-400 dark:hover:border-slate-500'"
                         @click="$refs.fileInput.click()">
                        <svg class="w-8 h-8 mx-auto mb-2" :class="dragOver ? 'text-[#E30613]' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m6-6H6"/></svg>
                        <p class="text-sm font-medium" :class="dragOver ? 'text-[#E30613]' : 'text-slate-500 dark:text-slate-400'">Attach File (optional)</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">PNG, JPG, PDF, or ZIP</p>
                    </div>
                    <p x-show="fileError" x-cloak class="mt-2 text-xs text-red-500" aria-live="polite" x-text="fileError"></p>

                    <div class="flex justify-end mt-3">
                        <button type="submit" :disabled="posting" class="px-4 py-2 text-sm font-medium text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-all duration-200 disabled:opacity-60 flex items-center gap-2">
                            <span x-show="!posting">Post Comment</span>
                            <svg x-show="posting" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M12 2v6"/></svg>
                            <span x-show="posting">Posting…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ticket Info</h2>
                </div>
                <div class="p-4 space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Category</span>
                        <span class="text-slate-900 dark:text-white font-medium">{{ $ticket->category->name ?? '-' }}</span>
                    </div>
                    @if($ticket->subCategory)
                    <div class="flex justify-between">
                        <span class="text-slate-500">Subcategory</span>
                        <span class="text-slate-900 dark:text-white font-medium">{{ $ticket->subCategory->name }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-slate-500">Reporter</span>
                        <span class="text-slate-900 dark:text-white font-medium">{{ $ticket->user->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Assignee</span>
                        <span class="text-slate-900 dark:text-white font-medium">{{ $ticket->assignee->name ?? 'Unassigned' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Created</span>
                        <span class="text-slate-900 dark:text-white font-medium">{{ $ticket->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- SLA --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">SLA</h2>
                </div>
                <div class="p-4 space-y-2.5 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Priority</span>
                        @if($hasSla)
                        <span class="px-2 py-0.5 rounded text-xs font-medium border {{ $slaColors[$ticket->sla_priority] ?? '' }}">{{ ucfirst($ticket->sla_priority) }}</span>
                        @else
                        <span class="text-slate-400">No SLA</span>
                        @endif
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Resolution Time</span>
                        <span class="text-slate-900 dark:text-white font-medium">{{ $hasSla ? ($ticket->sla_deadline->diffInDays($ticket->sla_started_at) ?: 1) . ' Day' . ($ticket->sla_deadline->diffInDays($ticket->sla_started_at) > 1 ? 's' : '') : '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Started</span>
                        <span class="text-slate-900 dark:text-white font-medium">{{ $hasSla ? $ticket->sla_started_at->format('d M Y, H:i') : '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Deadline</span>
                        <span class="text-slate-900 dark:text-white font-medium">{{ $hasSla ? $ticket->sla_deadline->format('d M Y, H:i') : '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Status</span>
                        @if($hasSla)
                            @if($ticket->sla_status === 'No SLA')
                            <span class="text-slate-400">No SLA</span>
                            @elseif($ticket->sla_status === 'Not Evaluated')
                            <span class="text-slate-400">—</span>
                            @elseif($ticket->sla_status === 'Excellent')
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-600 border border-emerald-200 dark:border-emerald-900/30">Excellent</span>
                            @elseif($ticket->sla_status === 'Normal')
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-600 border border-blue-200 dark:border-blue-900/30">Normal</span>
                            @elseif($ticket->sla_status === 'Poor')
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-red-500/10 text-red-600 border-red-200 dark:border-red-900/30">Poor</span>
                            @endif
                        @else
                        <span class="text-slate-400">No SLA</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Timeline</h2>
                </div>
                <div class="p-4 sm:p-5">
                    <ol class="relative">
                        {{-- Connector: 1px hairline behind the markers --}}
                        <span class="absolute left-[13px] top-3 bottom-3 w-px bg-slate-200 dark:bg-slate-700" aria-hidden="true"></span>
                        @php $reopenCycles = 0; @endphp
                        @foreach($timeline as $event)
                            @php
                                $isReopen = $event['label'] === 'Re-opened';
                                if ($isReopen) $reopenCycles++;
                                $marker = $timelineMarkers[$event['label']] ?? 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400';
                                $icon = $timelineIcons[$event['label']] ?? 'M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75';
                            @endphp

                            @if($isReopen)
                            {{-- Lifecycle cycle divider — count derived from event sequence --}}
                            <li class="relative z-10 flex items-center gap-3 py-1" aria-hidden="true">
                                <span class="h-px flex-1 bg-gradient-to-r from-transparent via-orange-200 to-orange-200 dark:via-orange-900/50 dark:to-orange-900/50"></span>
                                <span class="text-[10px] font-semibold uppercase tracking-widest whitespace-nowrap text-orange-600/90 dark:text-orange-400/90">Reopen Cycle @if($reopenCycles > 1){{ $reopenCycles }}@endif</span>
                                <span class="h-px flex-1 bg-gradient-to-l from-transparent via-orange-200 to-orange-200 dark:via-orange-900/50 dark:to-orange-900/50"></span>
                            </li>
                            @endif

                            <li class="relative pb-5 last:pb-0">
                                <div class="flex items-start gap-3">
                                    {{-- Marker: icon chip, ring matches card background --}}
                                    <span class="relative z-10 mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full ring-4 ring-white dark:ring-slate-800 {{ $marker }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                                    </span>
                                    <div class="min-w-0 flex-1 {{ $event['label'] === 'Completed' ? 'rounded-lg bg-emerald-50/70 px-3 py-2 ring-1 ring-emerald-100 dark:bg-emerald-950/20 dark:ring-emerald-900/40' : 'pt-0.5' }}">
                                        <p class="text-sm font-semibold leading-snug break-words {{ $isReopen ? 'text-orange-700 dark:text-orange-300' : 'text-slate-900 dark:text-white' }}">{{ $event['label'] }}</p>
                                        <p class="mt-0.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400 break-words">
                                            @if($event['actor']){{ $event['actor'] }} · @endif{{ $event['timestamp']->format('d M Y, H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function commentForm() {
    return {
        // Editor state
        editorEmpty: true,
        editorFocused: false,
        plainTextComment: '',
        
        // Files
        fileNames: [],
        fileError: '',
        posting: false,
        dragOver: false,
        
        // Mention dropdown
        showMentionDropdown: false,
        mentionResults: [],
        mentionLoading: false,
        selectedMentionIndex: -1,
        dropdownTop: 0,
        dropdownLeft: 0,
        
        // Mention tracking
        mentionTimeout: null,
        mentionAbortController: null,
        lastMentionQuery: '',
        atSymbolPos: -1,
        savedRange: null,

        init() {
            this.updatePlaceholder();
            
            // Save range before blur to preserve selection for mention insertion
            const editor = this.$refs.commentEditor;
            editor.addEventListener('blur', () => {
                const sel = window.getSelection();
                if (sel.rangeCount > 0) {
                    this.savedRange = sel.getRangeAt(0).cloneRange();
                }
            }, true);
        },

        // Frontend whitelist mirrors the backend mimes:png,jpg,jpeg,pdf,zip rule.
        isAllowed(file) {
            return ['png', 'jpg', 'jpeg', 'pdf', 'zip'].includes(file.name.split('.').pop().toLowerCase());
        },

        applyFiles(fileList) {
            const ok = Array.from(fileList).filter(f => this.isAllowed(f));
            this.fileError = ok.length < fileList.length
                ? 'Unsupported file type. Allowed: PNG, JPG, PDF, ZIP.'
                : '';
            const dt = new DataTransfer();
            ok.forEach(f => dt.items.add(f));
            this.$refs.fileInput.files = dt.files;
            this.fileNames = ok.map(f => f.name);
        },

        syncFiles() {
            this.applyFiles(this.$refs.fileInput.files);
        },

        handleDrop(e) {
            this.dragOver = false;
            const dt = e.dataTransfer;
            if (!dt.files.length) return;
            const merged = new DataTransfer();
            Array.from(this.$refs.fileInput.files).forEach(f => merged.items.add(f));
            Array.from(dt.files).forEach(f => merged.items.add(f));
            this.applyFiles(merged.files);
        },

        removeFile(idx) {
            const input = this.$refs.fileInput;
            const dt = new DataTransfer();
            Array.from(input.files).filter((_, i) => i !== idx).forEach(f => dt.items.add(f));
            input.files = dt.files;
            this.syncFiles();
        },

        syncEditorContent() {
            const editor = this.$refs.commentEditor;
            this.plainTextComment = editor.innerText || '';
            this.updatePlaceholder();
            this.handleEditorKeyup();
        },

        updatePlaceholder() {
            const editor = this.$refs.commentEditor;
            this.editorEmpty = !editor || editor.innerText.trim().length === 0;
        },

        handleEditorKeyup(e) {
            this.detectMentionTrigger();
        },

        handleEditorKeydown(e) {
            if (!this.showMentionDropdown) return;

            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.selectedMentionIndex = Math.min(this.selectedMentionIndex + 1, this.mentionResults.length - 1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.selectedMentionIndex = Math.max(this.selectedMentionIndex - 1, -1);
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (this.selectedMentionIndex >= 0 && this.mentionResults[this.selectedMentionIndex]) {
                        this.selectMention(this.mentionResults[this.selectedMentionIndex]);
                    }
                    break;
                case 'Escape':
                    e.preventDefault();
                    this.closeMentionDropdown();
                    break;
            }
        },

        detectMentionTrigger() {
            const editor = this.$refs.commentEditor;
            if (!editor) return;

            const sel = window.getSelection();
            if (sel.rangeCount === 0) return;

            const range = sel.getRangeAt(0);
            const preRange = range.cloneRange();
            preRange.selectNodeContents(editor);
            preRange.setEnd(range.endContainer, range.endOffset);
            const textBeforeCursor = preRange.toString();

            // Look for @ symbol
            const lastAtIndex = textBeforeCursor.lastIndexOf('@');
            if (lastAtIndex === -1) {
                this.closeMentionDropdown();
                return;
            }

            const afterAt = textBeforeCursor.substring(lastAtIndex + 1);
            
            // Check for valid mention query (no spaces, special chars)
            if (!/^[a-zA-Z0-9_\-\.]*$/.test(afterAt)) {
                this.closeMentionDropdown();
                return;
            }

            if (afterAt.length < 1) {
                this.closeMentionDropdown();
                return;
            }

            this.atSymbolPos = lastAtIndex;
            
            if (this.lastMentionQuery !== afterAt) {
                this.fetchMentionUsers(afterAt);
            }

            this.updateDropdownPosition();
        },

        async fetchMentionUsers(query) {
            if (this.mentionAbortController) {
                this.mentionAbortController.abort();
            }

            clearTimeout(this.mentionTimeout);

            this.lastMentionQuery = query;
            this.mentionLoading = true;
            this.mentionAbortController = new AbortController();

            this.mentionTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/api/users/search?q=${encodeURIComponent(query)}`, {
                        credentials: 'same-origin',
                        signal: this.mentionAbortController.signal
                    });

                    if (!response.ok) {
                        this.mentionLoading = false;
                        return;
                    }

                    const users = await response.json();
                    
                    if (this.lastMentionQuery === query) {
                        this.mentionResults = users;
                        this.showMentionDropdown = users.length > 0;
                        this.selectedMentionIndex = -1;
                        this.mentionLoading = false;
                    }
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        this.mentionLoading = false;
                    }
                }
            }, 250);
        },

        closeMentionDropdown() {
            this.showMentionDropdown = false;
            this.mentionResults = [];
            this.lastMentionQuery = '';
            
            if (this.mentionAbortController) {
                this.mentionAbortController.abort();
                this.mentionAbortController = null;
            }
            
            clearTimeout(this.mentionTimeout);
        },

        updateDropdownPosition() {
            const editor = this.$refs.commentEditor;
            if (!editor) return;

            const sel = window.getSelection();
            if (sel.rangeCount === 0) return;

            const range = sel.getRangeAt(0).cloneRange();
            range.collapse(false);
            const rect = range.getBoundingClientRect();
            const editorRect = editor.getBoundingClientRect();

            this.dropdownTop = rect.bottom - editorRect.top + 5;
            this.dropdownLeft = Math.max(0, rect.left - editorRect.left);
        },

        selectMention(user) {
            const editor = this.$refs.commentEditor;
            
            // Get full current text
            const fullText = editor.innerText || '';
            
            // Use saved range to determine cursor position
            let range = this.savedRange || window.getSelection().getRangeAt(0);
            
            if (!range) {
                this.closeMentionDropdown();
                return;
            }

            // Calculate cursor position from range
            const preRange = range.cloneRange();
            preRange.selectNodeContents(editor);
            preRange.setEnd(range.endContainer, range.endOffset);
            const textBeforeCursor = preRange.toString();
            const cursorPos = textBeforeCursor.length;

            // Find @ position before cursor
            const beforeCursor = fullText.substring(0, cursorPos);
            const atPos = beforeCursor.lastIndexOf('@');
            
            if (atPos === -1) {
                this.closeMentionDropdown();
                return;
            }

            // Build new text: before@ + @name + space + afterMention
            const beforeAt = fullText.substring(0, atPos);
            const afterMention = fullText.substring(cursorPos);
            const newText = beforeAt + '@' + user.name + ' ' + afterMention;

            // Set as plain text first
            editor.innerText = newText;
            
            // Update form field
            this.plainTextComment = newText;

            // Apply HTML highlighting to the mention
             this.$nextTick(() => {
                 let html = editor.innerHTML;
                 // Match @name followed by space or end of string (lookahead, no word boundary for names with spaces)
                 const mentionPattern = new RegExp(`(@${user.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})(?=\\s|$)`, 'g');
                 html = html.replace(mentionPattern, '<span class="bg-[#E30613]/10 dark:bg-[#E30613]/20 text-[#E30613] dark:text-[#E30613] font-medium px-1 rounded inline-block">$1</span>');
                 editor.innerHTML = html;
                
                // Position cursor after the mention
                const targetPos = beforeAt.length + 1 + user.name.length + 1; // @name + space
                if (editor.firstChild) {
                    const sel = window.getSelection();
                    let currentPos = 0;
                    let found = false;
                    
                    function traverse(node) {
                        if (found) return;
                        if (node.nodeType === Node.TEXT_NODE) {
                            const nextPos = currentPos + node.length;
                            if (targetPos <= nextPos) {
                                const cursorRange = document.createRange();
                                cursorRange.setStart(node, Math.min(targetPos - currentPos, node.length));
                                cursorRange.collapse(true);
                                sel.removeAllRanges();
                                sel.addRange(cursorRange);
                                found = true;
                                return;
                            }
                            currentPos = nextPos;
                        } else {
                            for (let child of node.childNodes) {
                                traverse(child);
                                if (found) return;
                            }
                        }
                    }
                    
                    traverse(editor);
                    editor.focus();
                }
            });

            this.closeMentionDropdown();
            this.savedRange = null;
        },

        handlePaste(e) {
            e.preventDefault();
            const text = e.clipboardData.getData('text/plain');
            document.execCommand('insertText', false, text);
            this.$nextTick(() => {
                this.syncEditorContent();
            });
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }
}
</script>
@endpush
@endsection