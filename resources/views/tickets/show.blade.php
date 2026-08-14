@extends('layouts.app')

@section('title', $ticket->ticket_number . ' - MITO IT Helpdesk')

@php
    $canManage = Auth::user()->canManageTickets();
    $canEdit = in_array(auth()->user()->role?->slug, ['admin', 'manager']);
    $slaColors = [
        'low' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
        'medium' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
        'high' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400',
        'critical' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
    ];
    $statusColors = [
        'Waiting Confirmation' => 'bg-primary-50 text-primary-700 border border-primary-200 dark:bg-primary-500/10 dark:text-primary-400 dark:border-primary-500/30',
        'In Progress' => 'bg-warning-50 text-warning-700 border border-warning-200 dark:bg-warning-500/15 dark:text-warning-400 dark:border-warning-500/30',
        'Completed' => 'bg-success-50 text-success-700 border border-success-200 dark:bg-success-500/15 dark:text-success-400 dark:border-success-500/30',
    ];
    $priorityColors = [
        'low' => 'bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
        'medium' => 'bg-primary-50 text-primary-700 border border-primary-200 dark:bg-primary-500/10 dark:text-primary-400 dark:border-primary-500/30',
        'high' => 'bg-orange-50 text-orange-700 border border-orange-200 dark:bg-orange-500/15 dark:text-orange-400 dark:border-orange-500/30',
        'critical' => 'bg-danger-50 text-danger-700 border border-danger-200 dark:bg-danger-500/15 dark:text-danger-400 dark:border-danger-500/30',
    ];
    $timeline = $ticket->timeline;
    $slaStatus = $ticket->sla_status;
    $slaPerformance = $ticket->sla_performance;
@endphp

@section('content')
<div class="space-y-6">
    {{-- PAGE HEADER --}}
    <x-page-header :title="$ticket->ticket_number" :description="\Illuminate\Support\Str::limit($ticket->description ?? '', 120)">
        @slot('toolbar')
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide {{ $statusColors[$ticket->status] ?? '' }}">{{ $ticket->status }}</span>
                @if($canManage)
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide {{ $priorityColors[$ticket->priority] ?? '' }}">{{ $ticket->priority }}</span>
                @endif
            </div>
        @endslot
        @slot('actions')
            <a href="{{ route('tickets.index') }}" class="btn-secondary btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Tickets
            </a>
        @endslot
    </x-page-header>

    {{-- TIMELINE --}}
    <div class="card">
        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Timeline</h2>
        </div>
        <div class="px-5 py-4">
            <div class="relative">
                <div class="absolute left-2 top-2 bottom-2 w-px bg-slate-200 dark:bg-slate-700"></div>
                <div class="space-y-4">
                    @foreach($timeline as $index => $event)
                    <div class="relative flex items-start gap-4">
                        <div class="relative z-10 flex-shrink-0 mt-1">
                            <div class="w-4 h-4 rounded-full border-2 border-white dark:border-slate-800
                                @if($event['label'] === 'Completed')
                                    bg-success-500
                                @elseif($event['label'] === 'In Progress')
                                    bg-warning-500
                                @elseif($event['label'] === 'Problem Analysis')
                                    bg-primary-500
                                @elseif($event['label'] === 'Assigned')
                                    bg-purple-500
                                @else
                                    bg-slate-400
                                @endif
                            "></div>
                        </div>
                        <div class="flex-1 min-w-0 pb-4">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $event['label'] }}</span>
                                @if($event['actor'])
                                <span class="text-xs text-slate-500 dark:text-slate-400">&middot; {{ $event['actor'] }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $event['timestamp']->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Description + Comments + Workflow --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Description --}}
            <div class="card">
                <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Description</h2>
                </div>
                <div class="px-5 py-4">
                    <div class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $ticket->description }}</div>
                </div>
                @if($ticket->attachments->count() > 0)
                <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50 dark:border-slate-800 dark:bg-slate-900/50">
                    <div class="flex flex-wrap gap-2">
                        @foreach($ticket->attachments as $attachment)
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm dark:bg-slate-800 dark:border-slate-700">
                            <a href="{{ route('tickets.attachments.download', [$ticket->id, $attachment->id]) }}" class="inline-flex items-center gap-2 text-slate-700 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors group">
                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 group-hover:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span class="max-w-[180px] truncate" title="{{ $attachment->original_filename }}">{{ $attachment->original_filename }}</span>
                            </a>
                            <span class="text-slate-400 dark:text-slate-500 text-xs">{{ $attachment->formattedSize() }}</span>
                            <form method="POST" action="{{ route('tickets.attachments.destroy', [$ticket->id, $attachment->id]) }}" class="inline" onsubmit="return confirm('Delete this attachment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-300 dark:text-slate-600 hover:text-red-500 dark:hover:text-red-400 transition-colors p-0.5" title="Delete attachment" aria-label="Delete attachment">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Workflow (IT Support / management) --}}
            @if($canManage)
            <div class="card">
                <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Workflow</h2>
                </div>
                <div class="px-5 py-4">
                    @if($ticket->status === 'Completed')
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        This ticket has been completed and its workflow is locked.
                    </div>
                    @else
                    <form method="POST" action="{{ route('tickets.status.update', $ticket->id) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="wf_status" class="form-label">Status</label>
                            <select name="status" id="wf_status" class="select">
                                <option value="Waiting Confirmation" {{ $ticket->status === 'Waiting Confirmation' ? 'selected' : '' }}>Waiting Confirmation</option>
                                <option value="In Progress" {{ $ticket->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Completed" {{ $ticket->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div>
                            <label for="wf_analysis" class="form-label">Problem Analysis</label>
                            <textarea name="problem_analysis" id="wf_analysis" rows="3" class="textarea" placeholder="Root cause / investigation notes — required to move to In Progress or Completed">{{ old('problem_analysis', $ticket->problem_analysis) }}</textarea>
                            @error('problem_analysis')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="wf_resolution" class="form-label">Resolution</label>
                            <textarea name="resolution" id="wf_resolution" rows="3" class="textarea" placeholder="How the issue was fixed — required before Completed">{{ old('resolution', $ticket->resolution) }}</textarea>
                            @error('resolution')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        @error('assignee')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <div class="flex flex-wrap items-center gap-3 pt-1">
                            @if($ticket->status !== 'In Progress' && ($ticket->assignee_id === null || $ticket->assignee_id == auth()->id() || in_array(auth()->user()->role?->slug, ['admin', 'manager'])))
                            <button type="button" onclick="assignTicketToMe({{ $ticket->id }})" class="btn-primary btn-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Assign to Me
                            </button>
                            @endif
                            <button type="submit" class="btn-secondary btn-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Save Status
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
            @endif

            {{-- Problem Analysis & Resolution (read only for reporters) --}}
            @if(!$canManage && ($ticket->problem_analysis || $ticket->resolution))
            <div class="card">
                <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Support Notes</h2>
                </div>
                <div class="px-5 py-4 space-y-4">
                    @if($ticket->problem_analysis)
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500 mb-1">Problem Analysis</div>
                        <div class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $ticket->problem_analysis }}</div>
                    </div>
                    @endif
                    @if($ticket->resolution)
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500 mb-1">Resolution</div>
                        <div class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $ticket->resolution }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Comments --}}
            <div class="card">
                <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Comments ({{ $ticket->comments->count() }})</h2>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-slate-800">
                    @forelse($ticket->comments as $comment)
                    <div class="px-5 py-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($comment->user->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $comment->user->name }}</span>
                                    @if($comment->user->role)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $comment->user->role->name }}</span>
                                    @endif
                                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $comment->comment }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-8 text-center text-sm text-slate-400 dark:text-slate-500">
                        <svg class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        No comments yet. Be the first to comment.
                    </div>
                    @endforelse
                </div>

                {{-- Comment Form --}}
                <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50 dark:border-slate-800 dark:bg-slate-900/50">
                    <form method="POST" action="{{ route('tickets.comments.store', $ticket->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <div class="flex-1">
                                <textarea name="comment" rows="3" required class="input" placeholder="Add a comment...">{{ old('comment') }}</textarea>
                                <div class="flex items-center justify-between mt-3">
                                    <div>
                                        <label class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                            Attach files
                                            <input type="file" name="attachments[]" multiple class="hidden">
                                        </label>
                                    </div>
                                    <button type="submit" class="btn-primary btn-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                        Post Comment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: compact sidebar --}}
        <div class="space-y-4">
            {{-- Details --}}
            <div class="card">
                <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wide">Details</h3>
                </div>
                <div class="px-4 py-3">
                    <dl class="space-y-2.5 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Status</dt>
                            <dd>
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide {{ $statusColors[$ticket->status] ?? '' }}">{{ $ticket->status }}</span>
                            </dd>
                        </div>
                        @if($canManage)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Priority</dt>
                            <dd>
                                @if($canEdit)
                                <select onchange="updateTicketPriority({{ $ticket->id }}, this.value, this)" class="text-xs font-bold border-0 bg-transparent focus:ring-0 p-0 cursor-pointer text-right capitalize {{ $priorityColors[$ticket->priority] ?? '' }}">
                                    <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="critical" {{ $ticket->priority === 'critical' ? 'selected' : '' }}>Critical</option>
                                </select>
                                @else
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide {{ $priorityColors[$ticket->priority] ?? '' }}">{{ $ticket->priority }}</span>
                                @endif
                            </dd>
                        </div>
                        @endif
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Category</dt>
                            <dd class="font-semibold text-slate-900 dark:text-white text-right">{{ $ticket->category->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Sub Category</dt>
                            <dd class="font-semibold text-slate-900 dark:text-white text-right">{{ $ticket->subCategory->name ?? '-' }}</dd>
                        </div>
                        @if($canEdit)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Assignee</dt>
                            <dd class="min-w-0">
                                <select onchange="updateTicketAssignee({{ $ticket->id }}, this.value, this)" class="text-xs font-semibold border-0 bg-transparent focus:ring-0 p-0 cursor-pointer text-right text-slate-900 dark:text-white max-w-[140px]">
                                    <option value="">Unassigned</option>
                                    @foreach($staffUsers ?? [] as $staff)
                                    <option value="{{ $staff->id }}" {{ $ticket->assignee_id == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </dd>
                        </div>
                        @endif
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Reporter</dt>
                            <dd class="text-right">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $ticket->user->name }}</div>
                                <div class="text-xs text-slate-400 dark:text-slate-500">{{ $ticket->user->email }}</div>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- SLA --}}
            @if($ticket->sla_priority)
            <div class="card">
                <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wide">SLA</h3>
                </div>
                <div class="px-4 py-3">
                    <dl class="space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Priority</dt>
                            <dd>
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide {{ $slaColors[$ticket->sla_priority] ?? '' }}">{{ $ticket->sla_priority }}</span>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Started</dt>
                            <dd class="font-semibold text-slate-900 dark:text-white text-right">{{ $ticket->sla_started_at?->format('d M Y, H:i') ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Deadline</dt>
                            <dd class="font-semibold text-slate-900 dark:text-white text-right">{{ $ticket->sla_deadline?->format('d M Y, H:i') ?? '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Status</dt>
                            <dd>
                                @if($slaStatus === 'Met')
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400">Met</span>
                                @elseif($slaStatus === 'Breached')
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide bg-danger-50 text-danger-700 dark:bg-danger-500/15 dark:text-danger-400">Breached</span>
                                @elseif($slaStatus === 'Active')
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-400">Active</span>
                                @else
                                <span class="text-xs text-slate-400">-</span>
                                @endif
                            </dd>
                        </div>
                        @if($slaPerformance)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500 dark:text-slate-400">Performance</dt>
                            <dd>
                                @if($slaPerformance === 'EXCELLENT')
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400">Excellent</span>
                                @elseif($slaPerformance === 'NORMAL')
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-400">Normal</span>
                                @else
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide bg-danger-50 text-danger-700 dark:bg-danger-500/15 dark:text-danger-400">Poor</span>
                                @endif
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if($canManage)
<script>
function ticketPatch(url, payload, method) {
    method = method || 'PATCH';
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
    }).then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(data.error || 'Request failed (' + r.status + ')');
        return data;
    });
}

function ticketSelectError(select) {
    if (select.dataset.prev !== undefined && select.dataset.prev !== 'undefined') {
        select.value = select.dataset.prev;
    }
    window.MITO.toast('Could not update — please try again', 'error');
}

function assignTicketToMe(ticketId) {
    const analysis = document.getElementById('wf_analysis').value;
    ticketPatch(`/tickets/${ticketId}/assign-to-me`, { problem_analysis: analysis }, 'POST')
        .then(() => {
            window.MITO.toast('Ticket assigned to you and moved to In Progress', 'success');
            setTimeout(() => window.location.reload(), 700);
        })
        .catch(err => window.MITO.toast(err.message || 'Could not assign ticket', 'error'));
}

function updateTicketPriority(ticketId, value, select) {
    select.dataset.prev = select.value;
    ticketPatch(`/tickets/${ticketId}/priority`, { priority: value })
        .then(() => window.MITO.toast('Priority set to ' + value.charAt(0).toUpperCase() + value.slice(1), 'success'))
        .catch(() => ticketSelectError(select));
}

function updateTicketAssignee(ticketId, value, select) {
    select.dataset.prev = select.value;
    ticketPatch(`/tickets/${ticketId}/assign`, { assignee_id: value })
        .then(() => window.MITO.toast(value ? 'Assignee updated' : 'Ticket unassigned', 'success'))
        .catch(() => ticketSelectError(select));
}

function updateTicketSla(ticketId, value, select) {
    select.dataset.prev = select.value;
    ticketPatch(`/tickets/${ticketId}/sla`, { sla_priority: value })
        .then(() => window.MITO.toast(value ? 'SLA set to ' + value.charAt(0).toUpperCase() + value.slice(1) : 'SLA cleared', 'success'))
        .catch(() => ticketSelectError(select));
}
</script>
@endif
@endsection
