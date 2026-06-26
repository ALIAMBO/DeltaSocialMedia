@extends('layouts.app')
@section('title', 'Chat with ' . $user->name)

<!-- Scroll messages to bottom on page load -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('messages-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
});
</script>

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Chat Header -->
    <div class="bg-white dark:bg-gray-800 rounded-t-2xl border border-gray-100 dark:border-gray-700 shadow-sm px-4 py-3 flex items-center gap-3 transition-colors">
        <a href="{{ route('chat.index') }}" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <a href="{{ route('profile.show', $user) }}" class="flex items-center gap-2">
            <img src="{{ $user->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                 class="w-9 h-9 rounded-full object-cover border border-gray-200 dark:border-gray-600" alt="avatar">
            <div>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 hover:text-green-600 dark:hover:text-green-400 transition">{{ $user->name }}</p>
            </div>
        </a>
    </div>

    <!-- Messages Area -->
    <div id="messages-container"
         class="bg-white dark:bg-gray-900 border-x border-gray-100 dark:border-gray-700 px-4 py-4 space-y-3 overflow-y-auto transition-colors"
         style="height: 460px;">
        @forelse ($messages as $message)
            @php $isMine = $message->sender_id === auth()->id(); @endphp
            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }} items-end gap-2">
                @if (!$isMine)
                    <img src="{{ $message->sender->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                         class="w-7 h-7 rounded-full object-cover border border-gray-200 dark:border-gray-600" alt="avatar">
                @endif
                <div class="{{ $isMine ? 'bg-green-600 dark:bg-green-700 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100' }}
                             max-w-xs px-4 py-2.5 rounded-2xl {{ $isMine ? 'rounded-br-sm' : 'rounded-bl-sm' }} text-sm transition-colors">
                    {{ $message->body }}
                    <p class="text-xs mt-1 {{ $isMine ? 'text-green-200 dark:text-green-300' : 'text-gray-400 dark:text-gray-500' }} text-right">
                        {{ $message->created_at->format('h:i A') }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-400 dark:text-gray-500 text-sm py-8">
                Start your conversation with {{ $user->name }}!
            </p>
        @endforelse
    </div>

    <!-- Message Input -->
    <div class="bg-white dark:bg-gray-800 rounded-b-2xl border border-gray-100 dark:border-gray-700 shadow-sm px-4 py-3 transition-colors">
        <form action="{{ route('chat.send', $user) }}" method="POST" class="flex items-center gap-3">
            @csrf
            <img src="{{ auth()->user()->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                 class="w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-600 flex-shrink-0" alt="avatar">
            <input type="text" name="body"
                   placeholder="Type a message..."
                   autocomplete="off"
                   class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300 dark:focus:ring-green-500 placeholder-gray-500 dark:placeholder-gray-400 transition-colors">
            <button type="submit"
                    class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white p-2 rounded-full transition flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </form>
    </div>
</div>
@endsection
