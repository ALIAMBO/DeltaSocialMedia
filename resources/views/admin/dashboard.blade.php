@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        @php
        $cards = [
            ['label' => 'Total Users',   'value' => $stats['users'],        'color' => 'green',  'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['label' => 'Total Posts',   'value' => $stats['posts'],        'color' => 'blue',   'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
            ['label' => 'Total Stories', 'value' => $stats['stories'],      'color' => 'purple', 'icon' => 'M15 10l4.553-2.07A1 1 0 0121 8.845v6.31a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
            ['label' => 'Admins',        'value' => $stats['admins'],       'color' => 'yellow', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['label' => 'Banned',        'value' => $stats['banned'],       'color' => 'red',    'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
            ['label' => 'New (7 days)',  'value' => $stats['new_this_week'],'color' => 'teal',   'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
        ];
        $colors = [
            'green'  => ['bg' => 'bg-green-500/10',  'text' => 'text-green-400',  'icon' => 'text-green-500'],
            'blue'   => ['bg' => 'bg-blue-500/10',   'text' => 'text-blue-400',   'icon' => 'text-blue-500'],
            'purple' => ['bg' => 'bg-purple-500/10', 'text' => 'text-purple-400', 'icon' => 'text-purple-500'],
            'yellow' => ['bg' => 'bg-yellow-500/10', 'text' => 'text-yellow-400', 'icon' => 'text-yellow-500'],
            'red'    => ['bg' => 'bg-red-500/10',    'text' => 'text-red-400',    'icon' => 'text-red-500'],
            'teal'   => ['bg' => 'bg-teal-500/10',   'text' => 'text-teal-400',   'icon' => 'text-teal-500'],
        ];
        @endphp

        @foreach($cards as $card)
        @php $c = $colors[$card['color']]; @endphp
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-gray-500 font-medium">{{ $card['label'] }}</span>
                <div class="{{ $c['bg'] }} rounded-lg p-1.5">
                    <svg class="w-4 h-4 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-black {{ $c['text'] }}">{{ number_format($card['value']) }}</p>
        </div>
        @endforeach
    </div>

    {{-- Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Users --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <h2 class="text-sm font-bold text-white">Latest Users</h2>
                <a href="{{ route('admin.users') }}" class="text-xs text-green-400 hover:underline">View all →</a>
            </div>
            <div class="divide-y divide-gray-800">
                @foreach($recentUsers as $u)
                <div class="flex items-center gap-3 px-5 py-3">
                    <img src="{{ $u->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                         class="w-8 h-8 rounded-full object-cover border border-gray-700">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ $u->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $u->email }}</p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        @if($u->is_admin)
                            <span class="text-[10px] bg-green-600/20 text-green-400 border border-green-600/30 px-1.5 py-0.5 rounded-full font-bold">Admin</span>
                        @endif
                        <span class="text-[10px] text-gray-600">{{ $u->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Recent Posts --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <h2 class="text-sm font-bold text-white">Latest Posts</h2>
                <a href="{{ route('admin.posts') }}" class="text-xs text-green-400 hover:underline">View all →</a>
            </div>
            <div class="divide-y divide-gray-800">
                @foreach($recentPosts as $post)
                <div class="flex items-start gap-3 px-5 py-3">
                    <img src="{{ $post->user?->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                         class="w-8 h-8 rounded-full object-cover border border-gray-700 flex-shrink-0 mt-0.5">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-white">{{ $post->user?->name }}</p>
                        <p class="text-xs text-gray-400 line-clamp-2 mt-0.5">{{ $post->body }}</p>
                    </div>
                    <span class="text-[10px] text-gray-600 flex-shrink-0">{{ $post->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
