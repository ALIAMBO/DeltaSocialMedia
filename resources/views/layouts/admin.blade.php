<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — @yield('title', 'Dashboard') | {{ config('app.name') }}</title>
    <script>
        // Prevent FOUC — match user's dark mode preference
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-950 text-white min-h-screen flex font-sans antialiased">

    {{-- Left Sidebar --}}
    <aside class="w-56 min-h-screen bg-gray-900 border-r border-gray-800 flex flex-col fixed top-0 left-0 z-30">
        {{-- Logo --}}
        <div class="px-5 py-4 border-b border-gray-800">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-green-600 flex items-center justify-center text-xs font-black text-white">A</span>
                <span class="font-bold text-sm text-white">Admin Panel</span>
            </a>
            <p class="text-[10px] text-gray-500 mt-0.5">{{ config('app.name') }}</p>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-1">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors
                      {{ request()->routeIs('admin.dashboard') ? 'bg-green-600/20 text-green-400' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            <a href="{{ route('admin.users') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors
                      {{ request()->routeIs('admin.users*') ? 'bg-green-600/20 text-green-400' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Users
            </a>
            <a href="{{ route('admin.posts') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors
                      {{ request()->routeIs('admin.posts*') ? 'bg-green-600/20 text-green-400' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                Posts
            </a>
            <a href="{{ route('admin.stories') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors
                      {{ request()->routeIs('admin.stories*') ? 'bg-green-600/20 text-green-400' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.07A1 1 0 0121 8.845v6.31a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Stories
            </a>
        </nav>

        {{-- Footer --}}
        <div class="px-4 py-4 border-t border-gray-800 space-y-2">
            <a href="{{ route('feed') }}" class="flex items-center gap-2 text-xs text-gray-500 hover:text-white transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to App
            </a>
            <div class="flex items-center gap-2">
                <img src="{{ auth()->user()->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                     class="w-6 h-6 rounded-full object-cover border border-gray-700">
                <span class="text-xs text-gray-400 truncate">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="ml-56 flex-1 min-h-screen flex flex-col">
        {{-- Top bar --}}
        <header class="h-14 bg-gray-900/80 backdrop-blur border-b border-gray-800 flex items-center px-6 sticky top-0 z-20">
            <h1 class="text-sm font-bold text-white">@yield('title', 'Dashboard')</h1>
            <div class="ml-auto flex items-center gap-3">
                <span class="text-xs bg-green-600/20 text-green-400 border border-green-600/30 px-2 py-0.5 rounded-full font-semibold">Admin</span>
            </div>
        </header>

        {{-- Flash Messages --}}
        <div class="px-6 pt-4 space-y-2">
            @if(session('success'))
                <div class="bg-green-900/30 border border-green-700/50 text-green-300 text-sm rounded-xl px-4 py-3 admin-alert">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-900/30 border border-red-700/50 text-red-300 text-sm rounded-xl px-4 py-3 admin-alert">{{ session('error') }}</div>
            @endif
        </div>

        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.admin-alert').forEach(el => {
            setTimeout(() => {
                el.style.transition = 'opacity 0.3s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }, 4000);
        });
    });
    </script>
</body>
</html>
