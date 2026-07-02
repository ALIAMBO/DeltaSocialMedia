@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white transition-colors">Notifications</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and view all your activity notifications.</p>
        </div>
        
        @if (auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.readAll') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="text-xs font-semibold text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/10 px-3 py-1.5 rounded-full hover:bg-green-100 dark:hover:bg-green-900/20 transition-all shadow-sm">
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    <!-- Notification List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors">
        @if ($notifications->isEmpty())
            <div class="p-12 text-center text-gray-500 dark:text-gray-400 transition-colors">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-1">No notifications yet</p>
                <p class="text-sm">When others like, comment, or follow you, your notifications will appear here.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($notifications as $notification)
                    @php
                        $isUnread = $notification->unread();
                        $data = $notification->data;
                        
                        // Get performer details
                        $performerId = $data['follower_id'] ?? $data['liker_id'] ?? $data['commenter_id'] ?? null;
                        $performer = ($performerId && isset($performers[$performerId])) ? $performers[$performerId] : null;
                        
                        // Define notification type attributes
                        $iconBg = 'bg-gray-100 text-gray-600';
                        $iconHtml = '';
                        $link = '#';
                        
                        if ($notification->type === 'App\Notifications\NewFollowNotification') {
                            $iconBg = 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400';
                            $iconHtml = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>';
                            if ($performer) {
                                $link = route('profile.show', $performer);
                            }
                        } elseif ($notification->type === 'App\Notifications\NewLikeNotification') {
                            $iconBg = 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400';
                            $iconHtml = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>';
                            if (isset($data['post_id'])) {
                                $post = isset($posts[$data['post_id']]) ? $posts[$data['post_id']] : null;
                                if ($post) {
                                    $link = route('profile.show', $post->user_id) . '#post-' . $post->id;
                                }
                            }
                        } elseif ($notification->type === 'App\Notifications\NewCommentNotification') {
                            $iconBg = 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400';
                            $iconHtml = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>';
                            if (isset($data['post_id'])) {
                                $post = isset($posts[$data['post_id']]) ? $posts[$data['post_id']] : null;
                                if ($post) {
                                    $link = route('profile.show', $post->user_id) . '#post-' . $post->id;
                                }
                            }
                        }
                    @endphp

                    <div class="p-5 flex items-start gap-4 transition-all duration-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $isUnread ? 'bg-green-50/20 dark:bg-green-950/5' : '' }}">
                        <!-- Performer Avatar -->
                        <div class="flex-shrink-0">
                            @if ($performer)
                                <a href="{{ route('profile.show', $performer) }}">
                                    <img src="{{ $performer->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                                         alt="{{ $performer->name }}"
                                         class="w-11 h-11 rounded-full object-cover border border-gray-100 dark:border-gray-600 hover:opacity-85 transition-opacity">
                                </a>
                            @else
                                <img src="{{ asset('images/default-avatar.png') }}"
                                     alt="User"
                                     class="w-11 h-11 rounded-full object-cover border border-gray-100 dark:border-gray-600">
                            @endif
                        </div>

                        <!-- Notification Content & Metadata -->
                        <div class="flex-1 min-w-0">
                            <div class="text-sm text-gray-800 dark:text-gray-200 transition-colors">
                                @if ($performer)
                                    <a href="{{ route('profile.show', $performer) }}" class="font-bold hover:text-green-600 dark:hover:text-green-400 transition-colors">
                                        {{ $performer->name }}
                                    </a>
                                @else
                                    <span class="font-bold text-gray-500">Someone</span>
                                @endif
                                
                                <span class="text-gray-600 dark:text-gray-400">{{ $data['message'] ?? '' }}</span>
                                
                                @if ($notification->type === 'App\Notifications\NewCommentNotification' && isset($data['comment_body']))
                                    <div class="mt-2 text-xs bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 p-2.5 rounded-lg text-gray-600 dark:text-gray-300 italic line-clamp-2 max-w-xl transition-colors">
                                        "{{ $data['comment_body'] }}"
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Actions / Time -->
                            <div class="flex items-center gap-3 mt-1.5">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                                @if ($link !== '#')
                                    <span class="text-gray-300 dark:text-gray-600 text-xs">•</span>
                                    <a href="{{ $link }}" class="text-xs font-semibold text-green-600 dark:text-green-400 hover:underline">
                                        View Post/Profile
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Type Icon & Mark Read Action -->
                        <div class="flex items-center gap-3">
                            <!-- Type Icon badge -->
                            <div class="p-2 rounded-full {{ $iconBg }} shadow-sm flex-shrink-0">
                                {!! $iconHtml !!}
                            </div>
                            
                            <!-- Mark as read button -->
                            @if ($isUnread)
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="flex-shrink-0">
                                    @csrf
                                    <button type="submit" 
                                            class="text-gray-400 hover:text-green-600 dark:hover:text-green-400 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all"
                                            title="Mark as read">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if ($notifications->hasPages())
                <div class="p-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700 transition-colors">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
