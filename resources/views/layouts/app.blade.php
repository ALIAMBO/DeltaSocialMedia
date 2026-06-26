<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SocialMedia') }} – @yield('title', 'Home')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <!-- Vite assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 dark:bg-gray-900 font-sans antialiased transition-colors">

    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50 shadow-sm transition-colors">
        <div class="max-w-5xl mx-auto px-4 flex items-center justify-between h-14">
            <!-- Logo -->
            <a href="{{ route('feed') }}" class="text-green-600 dark:text-green-400 font-bold text-xl tracking-tight">
                {{ config('app.name') }}
            </a>

            @auth
            <!-- Navigation Links -->
            <div class="flex items-center gap-5 text-sm font-medium text-gray-600 dark:text-gray-300">
                <a href="{{ route('feed') }}"
                   class="hover:text-green-600 dark:hover:text-green-400 {{ request()->routeIs('feed') ? 'text-green-600 dark:text-green-400' : '' }} transition-colors">
                    Feed
                </a>
                <a href="{{ route('chat.index') }}"
                   class="hover:text-green-600 dark:hover:text-green-400 {{ request()->routeIs('chat.*') ? 'text-green-600 dark:text-green-400' : '' }} transition-colors">
                    Messages
                </a>

                <!-- Dark Mode Toggle -->
                <button id="theme-toggle" class="p-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-yellow-400 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors" title="Toggle dark mode">
                    <svg id="sun-icon" class="w-4 h-4 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1m-16 0H1m15.364 1.636l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg id="moon-icon" class="w-4 h-4 hidden dark:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 015.646 5.646 9.003 9.003 0 0015.354 20.354z"/>
                    </svg>
                </button>

                <!-- Avatar dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                        <img src="{{ auth()->user()->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                             alt="Avatar"
                             class="w-8 h-8 rounded-full object-cover border border-gray-300 dark:border-gray-600">
                        <span class="hidden sm:block">{{ auth()->user()->name }}</span>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         @click.away="open = false"
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg border border-gray-100 dark:border-gray-600 py-1 text-sm transition-colors">
                        <a href="{{ route('profile.show', auth()->user()) }}" class="block px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition-colors">Profile</a>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition-colors">Settings</a>
                        <hr class="my-1 dark:border-gray-600">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-600 text-red-500 dark:text-red-400 transition-colors">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endauth
        </div>
    </nav>

    <!-- Flash messages -->
    <div class="max-w-5xl mx-auto px-4 mt-4 space-y-2">
        @if (session('success'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-200 text-sm rounded-lg px-4 py-3 alert-message transition-colors" data-type="success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-200 text-sm rounded-lg px-4 py-3 alert-message transition-colors" data-type="error">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <!-- Page Content -->
    <main class="max-w-5xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    <!-- Alpine.js for dropdowns - MUST load before scripts that use x-data directives -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Dark Mode Toggle Script -->
    @vite('resources/js/dark-mode.js')
    
    <!-- Auto-close alert messages after 5 seconds -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert-message');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    });
    </script>
    
    @stack('scripts')
</body>
</html>
