@extends('layouts.app')
@section('title', 'Messages')

@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-xl font-bold text-gray-800 mb-5">Messages</h1>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">
        @forelse ($conversations as $conversationUser)
            <a href="{{ route('chat.show', $conversationUser) }}"
               class="flex items-center gap-3 p-4 hover:bg-gray-50 transition">
                <div class="relative">
                    <img src="{{ $conversationUser->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                         class="w-12 h-12 rounded-full object-cover border border-gray-200" alt="avatar">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-baseline">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $conversationUser->name }}</p>
                        @if ($conversationUser->last_message)
                            <span class="text-xs text-gray-400 flex-shrink-0 ml-2">
                                {{ $conversationUser->last_message->created_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                    @if ($conversationUser->last_message)
                        <p class="text-xs text-gray-500 truncate">
                            {{ $conversationUser->last_message->sender_id === auth()->id() ? 'You: ' : '' }}
                            {{ $conversationUser->last_message->body }}
                        </p>
                    @endif
                </div>
            </a>
        @empty
            <div class="p-8 text-center text-gray-400 text-sm">
                No conversations yet. Visit someone's profile and click "Message" to start chatting.
            </div>
        @endforelse
    </div>
</div>
@endsection
