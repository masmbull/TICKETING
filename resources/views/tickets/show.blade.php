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
    $hasSla = !is_null($ticket->sla_deadline) && in_array($ticket->sla_priority, $validPriorities);

    $timeline = $ticket->timeline;
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
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($ticket->attachments as $attachment)
                        <a href="{{ route('tickets.attachments.download', [$ticket->id, $attachment->id]) }}"
                           class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-600 dark:text-slate-300 hover:border-[#E30613] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            {{ $attachment->original_filename }}
                            <span class="text-xs text-slate-400">({{ $attachment->formattedSize() }})</span>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- WORKFLOW CARD --}}
            @if($canManage && !$isCompleted)
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
                            fetch('{{ route('tickets.assign', $ticket->id) }}', {
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
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Problem Analysis</label>
                            <p class="text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 whitespace-pre-wrap">{{ $ticket->problem_analysis ?? '—' }}</p>
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
                                fetch('{{ route('tickets.assign', $ticket->id) }}', {
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
                                fetch('{{ route('tickets.sla', $ticket->id) }}', {
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

            {{-- Completed notice --}}
            @if($isCompleted)
            <div class="bg-emerald-50 dark:bg-emerald-500/10 rounded-xl border border-emerald-200 dark:border-emerald-900/30 p-6 text-center">
                <svg class="w-10 h-10 mx-auto text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Ticket Closed / Completed</p>
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
                                <p class="text-sm text-slate-600 dark:text-slate-300 break-words">{{ $comment->comment }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center"><p class="text-sm text-slate-400">No comments yet</p></div>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('tickets.comments.store', $ticket->id) }}" class="p-3 border-t border-slate-200 dark:border-slate-700" x-data="{ posting: false }" @submit="posting = true">
                    @csrf
                    <div class="flex gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-[#E30613] flex items-center justify-center text-white font-bold text-xs flex-shrink-0">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
                        <div class="flex-1">
                            <textarea name="comment" rows="2" required class="w-full px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 resize-y" placeholder="Add a comment…" :disabled="posting"></textarea>
                            @error('comment')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="submit" :disabled="posting" class="px-3 py-1.5 text-xs font-medium text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-all duration-200 disabled:opacity-60 flex items-center gap-1.5">
                            <span x-show="!posting">Post Comment</span>
                            <svg x-show="posting" class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M12 2v6"/></svg>
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
                    @if($ticket->completed_at)
                    <div class="flex justify-between">
                        <span class="text-slate-500">Completed</span>
                        <span class="text-slate-900 dark:text-white font-medium">{{ $ticket->completed_at->format('d M Y, H:i') }}</span>
                    </div>
                    @endif
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
                            @if($ticket->sla_status === 'Met')
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-600 border border-emerald-200 dark:border-emerald-900/30">Met</span>
                            @elseif($ticket->sla_status === 'Breached')
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-red-500/10 text-red-600 border border-red-200 dark:border-red-900/30">Breached</span>
                            @else
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-600 border border-blue-200 dark:border-blue-900/30">Active</span>
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
                <div class="p-4">
                    <div class="relative pl-5">
                        <div class="absolute left-2 top-0 bottom-0 w-0.5 bg-slate-200 dark:bg-slate-700"></div>
                        <div class="space-y-4">
                            @foreach($timeline as $event)
                            <div class="relative pl-6">
                                <div class="absolute left-1.5 w-3 h-3 rounded-full border-2 border-white dark:border-slate-800 {{ $loop->first ? 'bg-[#E30613]' : 'bg-emerald-500' }}"></div>
                                <div>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $event['label'] }}</p>
                                    @if($event['actor'])<p class="text-xs text-slate-500 dark:text-slate-400">{{ $event['actor'] }}</p>@endif
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $event['timestamp']->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
