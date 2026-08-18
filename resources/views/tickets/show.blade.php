@extends('layouts.app')

@section('title', $ticket->ticket_number . ' - MITO IT Helpdesk')

@php
    $canManage = Auth::user()->canManageTickets();
    $isCompleted = $ticket->status === 'Completed';
    $isStaff = auth()->user()->isStaff();
    $isManager = auth()->user()->isAdmin() || auth()->user()->isManager();

    $statusColors = [
        'Waiting Confirmation' => 'bg-blue-500/10 text-blue-600 border-blue-200 dark:border-blue-900/30',
        'In Progress' => 'bg-amber-500/10 text-amber-600 border-amber-200 dark:border-amber-900/30',
        'Completed' => 'bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:border-emerald-900/30',
    ];
    $priorityColors = [
        'low' => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-700/50 dark:border-slate-600',
        'medium' => 'bg-blue-500/10 text-blue-600 border-blue-200 dark:border-blue-900/30',
        'high' => 'bg-orange-500/10 text-orange-600 border-orange-200 dark:border-orange-900/30',
        'critical' => 'bg-red-500/10 text-red-600 border-red-200 dark:border-red-900/30',
    ];
    $slaColors = $priorityColors;

    // A real SLA only applies when an active policy produced a deadline.
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
    <div class="flex items-center justify-between gap-3" style="animation-delay: 0ms">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route($canManage ? 'tickets.all' : 'tickets.index') }}"
               class="shrink-0 p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
               aria-label="Back">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white break-all">{{ $ticket->ticket_number }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ implode(' · ', $metaParts) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="px-3 py-1.5 rounded-lg text-sm font-medium border transition-all duration-200 {{ $statusColors[$ticket->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">{{ $ticket->status }}</span>
            @if($canManage && $ticket->priority)
            <span class="px-3 py-1.5 rounded-lg text-sm font-medium border transition-all duration-200 {{ $priorityColors[$ticket->priority] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">{{ ucfirst($ticket->priority) }}</span>
            @endif
        </div>
    </div>

    {{-- Two column layout --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 items-start">
        {{-- LEFT COLUMN --}}
        <div class="xl:col-span-2 space-y-4">
            {{-- Description --}}
            <div class="card animate-fade-in-up" style="animation-delay: 50ms">
                <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
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

            {{-- Workflow (manage) OR Support notes (viewer) --}}
            @if($canManage && !$isCompleted)
            <div class="card animate-fade-in-up" style="animation-delay: 100ms">
                <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Workflow</h2>
                    @if($ticket->assignee && $ticket->assignee_id != auth()->id() && !$isManager)
                    <span class="text-xs text-slate-500 dark:text-slate-400">Assigned to {{ $ticket->assignee->name }}</span>
                    @endif
                </div>
                <form method="POST" action="{{ route('tickets.status.update', $ticket->id) }}" class="p-4 space-y-3" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                                <option value="Waiting Confirmation" {{ $ticket->status === 'Waiting Confirmation' ? 'selected' : '' }}>Waiting Confirmation</option>
                                <option value="In Progress" {{ $ticket->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Completed" {{ $ticket->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2 flex items-end gap-2">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Assignee</label>
                                <select name="assignee_id" class="w-full px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                                    <option value="">Unassigned</option>
                                    @foreach($staffUsers ?? [] as $staff)
                                    <option value="{{ $staff->id }}" {{ $ticket->assignee_id == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($canManage)
                            <div x-data="{ go: false, run() {
                                go = true;
                                fetch('{{ route('tickets.assign-me', $ticket->id) }}', {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                                    body: JSON.stringify({})
                                })
                                  .then(r => r.ok ? r.json() : Promise.reject(r.json()))
                                  .then(() => { window.MITO.toast('Assigned to you.', 'success'); })
                                  .catch(() => { window.MITO.toast('Provide a problem analysis first.', 'error'); go = false; });
                            } }">
                                <button type="button" @click="run()" :disabled="go"
                                        class="h-full px-3 py-1.5 text-xs font-semibold text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-all duration-200 hover:brightness-110 active:scale-95 disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-[#E30613]/30 flex items-center gap-1.5">
                                    <svg x-show="!go" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17v5l-4-3-4 3v-5l4-2 4 2z"/></svg>
                                    <span x-show="!go">Assign to Me</span>
                                    <svg x-show="go" class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M12 2v6"/></svg>
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                    @if($isManager)
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
                            .then(d => {
                                this.saving = false;
                                window.MITO.toast('SLA updated successfully.', 'success');
                            })
                            .catch(e => {
                                this.saving = false;
                                window.MITO.toast(e.error || 'Failed to update SLA.', 'error');
                            });
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
                    @endif
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Problem Analysis</label>
                        <textarea name="problem_analysis" rows="2"
                                  class="w-full px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 resize-y"
                                  placeholder="Analysis notes...">{{ old('problem_analysis', $ticket->problem_analysis) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Resolution</label>
                        <textarea name="resolution" rows="2"
                                  class="w-full px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 resize-y"
                                  placeholder="Resolution details...">{{ old('resolution', $ticket->resolution) }}</textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="submitting"
                                class="px-4 py-1.5 text-sm font-medium text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-all duration-200 hover:brightness-110 active:scale-95 disabled:opacity-60 flex items-center gap-1.5">
                            <svg x-show="!submitting" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-show="!submitting">Save Changes</span>
                            <svg x-show="submitting" class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M12 2v6"/></svg>
                            <span x-show="submitting">Saving…</span>
                        </button>
                    </div>
                </form>
            </div>
            @elseif(!$canManage && ($ticket->problem_analysis || $ticket->resolution))
            <div class="card animate-fade-in-up" style="animation-delay: 100ms">
                <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Support Notes</h2>
                </div>
                <div class="p-4 space-y-3">
                    @if($ticket->problem_analysis)
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Problem Analysis</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-wrap">{{ $ticket->problem_analysis }}</p>
                    </div>
                    @endif
                    @if($ticket->resolution)
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Resolution</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-wrap">{{ $ticket->resolution }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Comments --}}
            <div class="card animate-fade-in-up" style="animation-delay: 150ms">
                <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Comments ({{ $ticket->comments->count() }})</h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($ticket->comments as $comment)
                    <div class="p-3">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-lg bg-[#E30613] flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                {{ strtoupper(substr($comment->user->name ?? '?', 0, 2)) }}
                            </div>
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
                    <div class="p-6 text-center">
                        <p class="text-sm text-slate-400">No comments yet</p>
                    </div>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('tickets.comments.store', $ticket->id) }}"
                      class="p-3 border-t border-slate-200 dark:border-slate-700"
                      x-data="{ posting: false }" @submit="posting = true">
                    @csrf
                    <div class="flex gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-[#E30613] flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="flex-1">
                            <textarea name="comment" rows="2" required
                                      class="w-full px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 resize-y"
                                      placeholder="Add a comment..."
                                      :disabled="posting"></textarea>
                            @error('comment')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="submit" :disabled="posting"
                                class="px-3 py-1.5 text-xs font-medium text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-all duration-200 hover:brightness-110 active:scale-95 disabled:opacity-60 flex items-center gap-1.5">
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
            {{-- Ticket Info --}}
            <div class="card animate-fade-in-up" style="animation-delay: 200ms">
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
                    @if($ticket->assignee)
                    <div class="flex justify-between">
                        <span class="text-slate-500">Assignee</span>
                        <span class="text-slate-900 dark:text-white font-medium">{{ $ticket->assignee->name }}</span>
                    </div>
                    @endif
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

            {{-- SLA (always rendered so labels are present) --}}
            <div class="card animate-fade-in-up" style="animation-delay: 250ms">
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
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-600 border border-emerald-200 dark:border-emerald-900/30 transition-colors duration-200">Met</span>
                            @elseif($ticket->sla_status === 'Breached')
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-red-500/10 text-red-600 border border-red-200 dark:border-red-900/30 transition-colors duration-200">Breached</span>
                            @else
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-600 border border-blue-200 dark:border-blue-900/30 transition-colors duration-200">Active</span>
                            @endif
                        @else
                        <span class="text-slate-400">No SLA</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="card animate-fade-in-up" style="animation-delay: 300ms">
                <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Timeline</h2>
                </div>
                <div class="p-4">
                    @if($timeline)
                    <div class="relative pl-5">
                        <div class="absolute left-2 top-0 bottom-0 w-0.5 bg-slate-200 dark:bg-slate-700"></div>
                        <div class="space-y-4">
                            @foreach($timeline as $event)
                            <div class="relative pl-6" style="animation-delay: {{ $loop->index * 50 }}ms">
                                <div class="absolute left-1.5 w-3 h-3 rounded-full border-2 border-white dark:border-slate-800 {{ $loop->first ? 'bg-[#E30613]' : 'bg-emerald-500' }} animate-fade-in-up"></div>
                                <div>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $event['label'] }}</p>
                                    @if($event['actor'])
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $event['actor'] }}</p>
                                    @endif
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $event['timestamp']->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <p class="text-xs text-slate-400">No timeline events yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
