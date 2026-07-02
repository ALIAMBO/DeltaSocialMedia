@extends('layouts.app')
@section('title', 'Search Users')

@section('content')
<div class="space-y-6">
    <!-- Search Box -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-colors">
        <form action="{{ route('search') }}" method="GET" class="flex gap-3">
            <input type="text" name="q" value="{{ $query }}" placeholder="Search users by name or email..."
                   class="flex-1 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-xl px-4 py-3 text-sm
                          focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500 transition-colors">
            <button type="submit"
                    class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white font-medium
                           px-6 py-3 rounded-xl transition-colors">
                Search
            </button>
        </form>
    </div>

    <!-- Results -->
    @if ($query)
        @if ($users->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center text-gray-500 dark:text-gray-400 transition-colors">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                No users found for "<strong>{{ $query }}</strong>"
            </div>
        @else
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Found {{ $users->count() }} user{{ $users->count() !== 1 ? 's' : '' }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($users as $user)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3 flex-1 min-w-0">
                                    <a href="{{ route('profile.show', $user) }}" class="flex-shrink-0">
                                        <img src="{{ $user->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                                             alt="{{ $user->name }}"
                                             class="w-12 h-12 rounded-full object-cover border border-gray-200 dark:border-gray-600 hover:opacity-80 transition-opacity">
                                    </a>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('profile.show', $user) }}" class="text-base font-semibold text-gray-900 dark:text-white hover:text-green-600 dark:hover:text-green-400 transition-colors block truncate">
                                            {{ $user->name }}
                                        </a>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                                        @if ($user->profile?->bio)
                                            <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2 mt-1">
                                                {{ $user->profile->bio }}
                                            </p>
                                        @endif
                                        <div class="flex gap-4 text-xs text-gray-600 dark:text-gray-400 mt-2">
                                            <span>{{ $user->posts_count }} Posts</span>
                                            <span>{{ $user->followers_count }} Followers</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ml-2">
                                    @php
                                        $isFollowing = auth()->user()->isFollowing($user);
                                    @endphp
                                    <form action="{{ route('follow.toggle', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button class="px-3 py-1.5 rounded-full text-xs font-semibold
                                                     {{ $isFollowing ? 'border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700' : 'bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white' }}
                                                     transition-colors whitespace-nowrap">
                                            {{ $isFollowing ? 'Following' : 'Follow' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center text-gray-500 dark:text-gray-400 transition-colors">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <p>Enter a name or email to search for users</p>
        </div>
    @endif
</div>
@endsection
