@extends('layouts.app')

@section('title', $ticket->ticket_number . ' - MITO IT Helpdesk')

@php
    $statusColors = [
        'Open' => 'bg-primary-50 text-primary-700 border border-primary-200',
        'In Progress' => 'bg-warning-50 text-warning-700 border border-warning-200',
        'Waiting User' => 'bg-orange-50 text-orange-700 border border-orange-200',
        'Resolved' => 'bg-success-50 text-success-700 border border-success-200',
        'Closed' => 'bg-slate-100 text-slate-500 border border-slate-200',
    ];
    $priorityColors = [
        'low' => 'bg-slate-100 text-slate-600 border border-slate-200',
        'medium' => 'bg-primary-50 text-primary-700 border border-primary-200',
        'high' => 'bg-orange-50 text-orange-700 border border-orange-200',
        'critical' => 'bg-danger-50 text-danger-700 border border-danger-200',
    ];
@endphp

@section('content')
<div class="min-h-screen -m-6 p-6 lg:p-8">
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('tickets.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900">{{ $ticket->ticket_number }}</h1>
                    <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide {{ $statusColors[$ticket->status] ?? '' }}">{{ $ticket->status }}</span>
                    <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide {{ $priorityColors[$ticket->priority] ?? '' }}">{{ $ticket->priority }}</span>
                </div>
                <p class="text-sm text-slate-500 mt-0.5">{{ $ticket->subject }}</p>
            </div>
        </div>

        @if(Auth::user()->isAdmin() || Auth::user()->isManager())
        <form method="POST" action="{{ route('tickets.status', $ticket->id) }}">
            @csrf
            @method('PATCH')
            <select name="status" onchange="this.form.submit()" class="select text-sm font-medium cursor-pointer">
                <option value="Open" {{ $ticket->status === 'Open' ? 'selected' : '' }}>Open</option>
                <option value="In Progress" {{ $ticket->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Waiting User" {{ $ticket->status === 'Waiting User' ? 'selected' : '' }}>Waiting User</option>
                <option value="Resolved" {{ $ticket->status === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="Closed" {{ $ticket->status === 'Closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </form>
        @endif
    </div>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Description + Comments --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Description --}}
            <div class="card">
                <div class="px-5 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-900">Description</h2>
                </div>
                <div class="px-5 py-4">
                    <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">{{ $ticket->description }}</div>
                </div>
                @if($ticket->attachments->count() > 0)
                <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                    <div class="flex flex-wrap gap-2">
                        @foreach($ticket->attachments as $attachment)
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span class="text-slate-700">{{ $attachment->original_filename }}</span>
                            <span class="text-slate-400 text-xs">({{ round($attachment->file_size / 1024, 1) }} KB)</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Comments --}}
            <div class="card">
                <div class="px-5 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-900">Comments ({{ $ticket->comments->count() }})</h2>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($ticket->comments as $comment)
                    <div class="px-5 py-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($comment->user->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-semibold text-slate-900">{{ $comment->user->name }}</span>
                                    @if($comment->user->role)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-slate-100 text-slate-500">{{ $comment->user->role->name }}</span>
                                    @endif
                                    <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">{{ $comment->comment }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-8 text-center text-sm text-slate-400">
                        <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        No comments yet. Be the first to comment.
                    </div>
                    @endforelse
                </div>

                {{-- Comment Form --}}
                <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
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
                                        <label class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-slate-700 cursor-pointer">
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

        {{-- Right: Sidebar Details --}}
        <div class="space-y-6">
            {{-- Details --}}
            <div class="card">
                <div class="px-5 py-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Details</h3>
                </div>
                <div class="px-5 py-4">
                    <dl class="space-y-4 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Status</dt>
                            <dd>
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide {{ $statusColors[$ticket->status] ?? '' }}">{{ $ticket->status }}</span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Priority</dt>
                            <dd>
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide {{ $priorityColors[$ticket->priority] ?? '' }}">{{ $ticket->priority }}</span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Category</dt>
                            <dd class="font-semibold text-slate-900">{{ $ticket->category->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Sub Category</dt>
                            <dd class="font-semibold text-slate-900">{{ $ticket->subCategory->name ?? '-' }}</dd>
                        </div>
                        @if($ticket->assignee)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Assignee</dt>
                            <dd class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center text-[10px] font-bold">{{ strtoupper(substr($ticket->assignee->name, 0, 1)) }}</div>
                                <span class="font-semibold text-slate-900">{{ $ticket->assignee->name }}</span>
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Reporter --}}
            <div class="card">
                <div class="px-5 py-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Reporter</h3>
                </div>
                <div class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold">
                            {{ strtoupper(substr($ticket->user->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-slate-900">{{ $ticket->user->name }}</div>
                            <div class="text-xs text-slate-400">{{ $ticket->user->email }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="card">
                <div class="px-5 py-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Timeline</h3>
                </div>
                <div class="px-5 py-4">
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-primary-500 flex-shrink-0"></div>
                            <div>
                                <div class="text-slate-700 font-medium">Created</div>
                                <div class="text-xs text-slate-400">{{ $ticket->created_at->format('d M Y, H:i') }}</div>
                            </div>
                        </div>
                        @if($ticket->first_response_at)
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-warning-500 flex-shrink-0"></div>
                            <div>
                                <div class="text-slate-700 font-medium">First Response</div>
                                <div class="text-xs text-slate-400">{{ $ticket->first_response_at->format('d M Y, H:i') }}</div>
                            </div>
                        </div>
                        @endif
                        @if($ticket->resolved_at)
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-success-500 flex-shrink-0"></div>
                            <div>
                                <div class="text-slate-700 font-medium">Resolved</div>
                                <div class="text-xs text-slate-400">{{ $ticket->resolved_at->format('d M Y, H:i') }}</div>
                            </div>
                        </div>
                        @endif
                        @if($ticket->closed_at)
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-slate-400 flex-shrink-0"></div>
                            <div>
                                <div class="text-slate-700 font-medium">Closed</div>
                                <div class="text-xs text-slate-400">{{ $ticket->closed_at->format('d M Y, H:i') }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
