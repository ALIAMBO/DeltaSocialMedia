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
<body class="bg-gray-100 font-sans antialiased">

    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 flex items-center justify-between h-14">
            <!-- Logo -->
            <a href="{{ route('feed') }}" class="text-green-600 font-bold text-xl tracking-tight">
                {{ config('app.name') }}
            </a>

            @auth
            <!-- Navigation Links -->
            <div class="flex items-center gap-5 text-sm font-medium text-gray-600">
                <a href="{{ route('feed') }}"
                   class="hover:text-green-600 {{ request()->routeIs('feed') ? 'text-green-600' : '' }}">
                    Feed
                </a>
                <a href="{{ route('chat.index') }}"
                   class="hover:text-green-600 {{ request()->routeIs('chat.*') ? 'text-green-600' : '' }}">
                    Messages
                </a>

                <!-- Avatar dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                        <img src="{{ auth()->user()->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                             alt="Avatar"
                             class="w-8 h-8 rounded-full object-cover border border-gray-300">
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
                         class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-100 py-1 text-sm">
                        <a href="{{ route('profile.show', auth()->user()) }}" class="block px-4 py-2 hover:bg-gray-50">Profile</a>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-50">Settings</a>
                        <hr class="my-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-50 text-red-500">
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
            <div class="bg-green-50 border border-green-300 text-green-800 text-sm rounded-lg px-4 py-3 alert-message" data-type="success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border border-red-300 text-red-800 text-sm rounded-lg px-4 py-3 alert-message" data-type="error">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <!-- Page Content -->
    <main class="max-w-5xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    @stack('scripts')
    <!-- Alpine.js for dropdowns -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
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
</body>
</html>
