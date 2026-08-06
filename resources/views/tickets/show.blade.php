@extends('layouts.app')

@section('title', 'Ticket ' . $ticket->ticket_number . ' - MITO IT Helpdesk')

@section('content')
<div class="space-y-6">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center space-x-3" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            <button type="button" class="ml-auto text-green-600 hover:text-green-800" @click="show = false">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center space-x-3">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-red-800">{{ $errors->first('status') ?: $errors->first('comment') }}</p>
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('tickets.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Ticket Detail</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $ticket->ticket_number }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            @php
                $statusColors = [
                    'Open' => 'bg-green-100 text-green-800',
                    'In Progress' => 'bg-blue-100 text-blue-800',
                    'Waiting User' => 'bg-amber-100 text-amber-800',
                    'Resolved' => 'bg-purple-100 text-purple-800',
                    'Closed' => 'bg-gray-100 text-gray-800',
                ];
                $priorityColors = [
                    'low' => 'bg-gray-100 text-gray-800',
                    'medium' => 'bg-blue-100 text-blue-800',
                    'high' => 'bg-amber-100 text-amber-800',
                    'critical' => 'bg-red-100 text-red-800',
                ];
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-800' }}">
                {{ ucfirst($ticket->priority) }}
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-800' }}">
                {{ $ticket->status }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Subject -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Subject</h2>
                <p class="text-gray-900 text-base">{{ $ticket->subject }}</p>
            </div>

            <!-- Description -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Description</h2>
                <div class="text-gray-700 text-sm leading-relaxed whitespace-pre-wrap">{{ $ticket->description }}</div>
            </div>

            <!-- Ticket Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ticket Actions</h2>
                <form action="{{ route('tickets.status', $ticket->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Update Status</label>
                        <div class="flex items-center space-x-3">
                            <select name="status" id="status" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="Open" {{ $ticket->status === 'Open' ? 'selected' : '' }}>Open</option>
                                <option value="In Progress" {{ $ticket->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Waiting User" {{ $ticket->status === 'Waiting User' ? 'selected' : '' }}>Waiting User</option>
                                <option value="Resolved" {{ $ticket->status === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="Closed" {{ $ticket->status === 'Closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Update Status
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Comments Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Comments</h2>

                <!-- Existing Comments -->
                @if($ticket->comments->count() > 0)
                    <div class="space-y-4 mb-6">
                        @foreach($ticket->comments as $comment)
                            <div class="flex space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center">
                                        <span class="text-xs font-bold text-white">{{ strtoupper(substr($comment->user->name, 0, 1)) }}</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <p class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</p>
                                        <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->comment }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 mb-6">No comments yet. Be the first to comment.</p>
                @endif

                <!-- New Comment Form -->
                <div class="border-t border-gray-200 pt-4">
                    <form action="{{ route('tickets.comments.store', $ticket->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Add a Comment</label>
                            <textarea name="comment" id="comment" rows="3"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                placeholder="Type your comment here...">{{ old('comment') }}</textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                Post Comment
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Timeline -->
            @php
                $timeline = session('timeline_' . $ticket->id, []);
            @endphp
            @if(count($timeline) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @foreach($timeline as $index => $entry)
                                <li>
                                    <div class="relative pb-8">
                                        @if($index < count($timeline) - 1)
                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        Status changed
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$entry['old_status']] ?? 'bg-gray-100 text-gray-800' }}">
                                                            {{ $entry['old_status'] }}
                                                        </span>
                                                        <svg class="w-4 h-4 text-gray-400 inline mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                        </svg>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$entry['new_status']] ?? 'bg-gray-100 text-gray-800' }}">
                                                            {{ $entry['new_status'] }}
                                                        </span>
                                                    </p>
                                                    <p class="mt-1 text-sm text-gray-500">by {{ $entry['user'] }}</p>
                                                </div>
                                                <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                    <time>{{ \Carbon\Carbon::parse($entry['timestamp'])->format('M d, Y \a\t h:i A') }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Ticket Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ticket Information</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket Number</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $ticket->ticket_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $ticket->status }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $ticket->category->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Sub-Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $ticket->subCategory->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $ticket->user->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $ticket->created_at->format('M d, Y \a\t h:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $ticket->updated_at->format('M d, Y \a\t h:i A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection