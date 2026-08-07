@extends('layouts.app')

@section('title', $ticket->ticket_number . ' - MITO IT Helpdesk')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('tickets.index')" class="text-gray-500 hover:text-gray-700">←</a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $ticket->ticket_number }}</h1>
            @php
                $statusColors = [
                    'Open' => 'bg-blue-100 text-blue-800',
                    'In Progress' => 'bg-yellow-100 text-yellow-800',
                    'Waiting User' => 'bg-orange-100 text-orange-800',
                    'Resolved' => 'bg-green-100 text-green-800',
                    'Closed' => 'bg-gray-100 text-gray-800',
                ];
                $priorityColors = [
                    'low' => 'bg-gray-100 text-gray-700',
                    'medium' => 'bg-blue-100 text-blue-700',
                    'high' => 'bg-orange-100 text-orange-700',
                    'critical' => 'bg-red-100 text-red-700',
                ];
            @endphp
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $ticket->status }}</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($ticket->priority) }}</span>
        </div>

        @if(Auth::user()->isAdmin() || Auth::user()->isManager())
        <form method="POST" action="{{ route('tickets.status', $ticket->id) }}">
            @csrf
            @method('PATCH')
            <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="Open" {{ $ticket->status === 'Open' ? 'selected' : '' }}>Open</option>
                <option value="In Progress" {{ $ticket->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Waiting User" {{ $ticket->status === 'Waiting User' ? 'selected' : '' }}>Waiting User</option>
                <option value="Resolved" {{ $ticket->status === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="Closed" {{ $ticket->status === 'Closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </form>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ $ticket->subject }}</h2>
                <div class="text-sm text-gray-500 mb-4">
                    Opened {{ $ticket->created_at->diffForHumans() }} by {{ $ticket->user->name }}
                </div>
                <div class="prose prose-sm max-w-none text-gray-700">
                    {!! nl2br(e($ticket->description)) !!}
                </div>
            </div>

            @if($ticket->attachments->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Attachments ({{ $ticket->attachments->count() }})</h3>
                <div class="space-y-2">
                    @foreach($ticket->attachments as $attachment)
                    <div class="flex items-center gap-2 text-sm p-2 bg-gray-50 rounded-md">
                        <span class="text-gray-400">📎</span>
                        <span class="text-gray-700">{{ $attachment->original_filename }}</span>
                        <span class="text-gray-400 text-xs">({{ round($attachment->file_size / 1024, 1) }} KB)</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Comments ({{ $ticket->comments->count() }})</h3>
                <div class="space-y-4 mb-6">
                    @forelse($ticket->comments as $comment)
                    <div class="border-l-2 border-blue-200 pl-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                            <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-gray-700">{!! nl2br(e($comment->comment)) !!}</p>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500">No comments yet.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('tickets.comments.store', $ticket->id) }}">
                    @csrf
                    <textarea name="comment" rows="3" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm mb-3" placeholder="Add a comment...">{{ old('comment') }}</textarea>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Post Comment</button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd class="font-medium text-gray-900">{{ $ticket->status }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Priority</dt><dd class="font-medium text-gray-900">{{ ucfirst($ticket->priority) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Category</dt><dd class="font-medium text-gray-900">{{ $ticket->category->name ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Sub Category</dt><dd class="font-medium text-gray-900">{{ $ticket->subCategory->name ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Created</dt><dd class="font-medium text-gray-900">{{ $ticket->created_at->format('d M Y, H:i') }}</dd></div>
                    @if($ticket->assignee)
                    <div class="flex justify-between"><dt class="text-gray-500">Assigned to</dt><dd class="font-medium text-gray-900">{{ $ticket->assignee->name }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection