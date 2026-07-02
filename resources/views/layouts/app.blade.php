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
    <!-- Prevent flash of light mode in dark mode -->
    <script>
        if (localStorage.getItem('darkMode') === 'true' ||
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <!-- Vite assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 dark:bg-gray-900 font-sans antialiased transition-colors">

    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50 shadow-sm transition-colors">
        <div class="max-w-6xl mx-auto px-4 flex items-center justify-between h-14">
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
                <a href="{{ route('notifications.index') }}"
                   class="relative p-1 hover:text-green-600 dark:hover:text-green-400 {{ request()->routeIs('notifications.index') ? 'text-green-600 dark:text-green-400' : '' }} transition-colors flex items-center"
                   title="Notifications">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if (auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full min-w-[16px] h-4 flex items-center justify-center border border-white dark:border-gray-800 shadow-sm leading-none">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>

                <!-- Search -->
                <form action="{{ route('search') }}" method="GET" class="hidden sm:block">
                    <input type="text" name="q" placeholder="Search users..."
                           class="border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-lg px-3 py-1.5 text-xs
                                  focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500 transition-colors">
                </form>

                <!-- Search mobile link -->
                <a href="{{ route('search') }}" class="sm:hidden hover:text-green-600 dark:hover:text-green-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
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
                    <div x-show="open" x-cloak
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
    <div class="max-w-6xl mx-auto px-4 mt-4 space-y-2">
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
    <main class="max-w-6xl mx-auto px-4 py-6">
        @auth
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <!-- Left Sidebar (Desktop only) -->
                <aside class="hidden md:block md:col-span-1 space-y-4">
                    <!-- User Mini Profile -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 transition-colors">
                        <div class="flex items-center gap-3">
                            <img src="{{ auth()->user()->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                                 alt="Avatar"
                                 class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-700">
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('profile.show', auth()->user()) }}" class="font-bold text-sm text-gray-900 dark:text-white hover:underline truncate block">
                                    {{ auth()->user()->name }}
                                </a>
                                <span class="text-xs text-gray-500 dark:text-gray-400 truncate block">
                                    {{ auth()->user()->email }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Navigation Links -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-3 shadow-sm border border-gray-100 dark:border-gray-700 transition-colors">
                        <nav class="space-y-1">
                            <a href="{{ route('feed') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ request()->routeIs('feed') ? 'bg-green-50 dark:bg-green-950/20 text-green-600 dark:text-green-400' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>Feed</span>
                            </a>
                            <a href="{{ route('chat.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ request()->routeIs('chat.*') ? 'bg-green-50 dark:bg-green-950/20 text-green-600 dark:text-green-400' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span>Messages</span>
                            </a>
                            <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ request()->routeIs('notifications.*') ? 'bg-green-50 dark:bg-green-950/20 text-green-600 dark:text-green-400' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span>Notifications</span>
                            </a>
                            <a href="{{ route('profile.show', auth()->user()) }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ request()->routeIs('profile.show') && request()->route('user') && request()->route('user')->id === auth()->id() ? 'bg-green-50 dark:bg-green-950/20 text-green-600 dark:text-green-400' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>My Profile</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ request()->routeIs('profile.edit') ? 'bg-green-50 dark:bg-green-950/20 text-green-600 dark:text-green-400' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Settings</span>
                            </a>
                            @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ request()->routeIs('admin.*') ? 'bg-green-50 dark:bg-green-950/20 text-green-600 dark:text-green-400' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Admin Panel</span>
                            </a>
                            @endif
                        </nav>
                    </div>

                    <!-- Placeholder for Future Features -->
                    <div class="bg-gray-50 dark:bg-gray-800/40 border border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-4 text-center text-xs text-gray-400 dark:text-gray-500 transition-colors">
                        <p class="font-semibold text-gray-500 dark:text-gray-400">Future Features</p>
                        <p class="mt-1">Additional features, widgets, and links will appear here.</p>
                        <div class="mt-3 flex items-center justify-center gap-1.5 py-1 px-3 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 font-medium rounded-full w-max mx-auto text-[10px] border border-emerald-200/50 dark:border-emerald-800/30">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                            Database Status: Persistent & Connected
                        </div>
                    </div>
                </aside>

                <!-- Page Content Area -->
                <div class="col-span-1 md:col-span-4">
                    @yield('content')
                </div>
            </div>
        @else
            @yield('content')
        @endauth
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

    @auth
    <!-- Floating Chat Widget -->
    <div x-data="{
        isOpen: false,
        activeView: 'inbox',
        conversations: [],
        contacts: [],
        messages: [],
        chatUser: null,
        newMessageText: '',
        unreadTotal: 0,
        pollingTimer: null,

        init() {
            this.fetchConversations();
            this.pollingTimer = setInterval(() => {
                this.pollUpdates();
            }, 5000);
        },

        fetchConversations() {
            fetch('/api/chat/conversations')
                .then(res => res.json())
                .then(data => {
                    this.conversations = data;
                    this.calculateUnread();
                });
        },

        fetchContacts() {
            fetch('/api/chat/contacts')
                .then(res => res.json())
                .then(data => {
                    this.contacts = data;
                });
        },

        openChat(user) {
            this.chatUser = user;
            this.activeView = 'chat';
            this.fetchMessages();
        },

        fetchMessages() {
            if (!this.chatUser) return;
            fetch('/api/chat/messages/' + this.chatUser.id)
                .then(res => res.json())
                .then(data => {
                    this.messages = data.messages;
                    this.chatUser = data.user;
                    this.scrollToBottom();
                    this.fetchConversations();
                });
        },

        sendMessage() {
            if (!this.newMessageText.trim() || !this.chatUser) return;
            let bodyText = this.newMessageText;
            this.newMessageText = '';

            fetch('/api/chat/messages/' + this.chatUser.id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content')
                },
                body: JSON.stringify({ body: bodyText })
            })
            .then(res => res.json())
            .then(msg => {
                this.messages.push(msg);
                this.scrollToBottom();
                this.fetchConversations();
            });
        },

        pollUpdates() {
            this.fetchConversations();
            if (this.isOpen && this.activeView === 'chat') {
                this.fetchMessages();
            }
        },

        calculateUnread() {
            this.unreadTotal = this.conversations.reduce((sum, c) => sum + (c.unread_count || 0), 0);
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messageContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },

        closeWidget() {
            this.isOpen = false;
        },

        toggleWidget() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.fetchConversations();
                if (this.activeView === 'chat') {
                    this.fetchMessages();
                }
            }
        },

        showInbox() {
            this.activeView = 'inbox';
            this.chatUser = null;
            this.fetchConversations();
        },

        showContacts() {
            this.activeView = 'contacts';
            this.chatUser = null;
            this.fetchContacts();
        }
    }" class="fixed bottom-6 right-6 z-50 font-sans" x-cloak>
        
        <!-- 1. Floating Trigger Button -->
        <button @click="toggleWidget()" 
                class="w-14 h-14 rounded-full bg-gradient-to-tr from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white flex items-center justify-center shadow-2xl transition-all duration-300 transform hover:scale-105 focus:outline-none relative">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <!-- Unread count badge -->
            <span x-show="unreadTotal > 0" 
                  x-text="unreadTotal" 
                  class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center border-2 border-white dark:border-gray-800 shadow-sm transition-all">
            </span>
        </button>

        <!-- 2. Chat Popover Panel -->
        <div x-show="isOpen" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0 translate-y-4 scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 scale-100" 
             x-transition:leave="transition ease-in duration-150" 
             x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             class="absolute bottom-16 right-0 w-[360px] h-[480px] bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-2xl flex flex-col overflow-hidden transition-colors">
            
            <!-- Panel Header -->
            <div class="p-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between transition-colors">
                
                <!-- Header: Inbox / Contacts view -->
                <div x-show="activeView !== 'chat'" class="flex gap-4">
                    <button @click="showInbox()" 
                            class="text-sm font-bold pb-1 transition-colors border-b-2" 
                            :class="activeView === 'inbox' ? 'text-green-600 dark:text-green-400 border-green-500' : 'text-gray-400 border-transparent hover:text-gray-600 dark:hover:text-gray-300'">
                        Inbox
                    </button>
                    <button @click="showContacts()" 
                            class="text-sm font-bold pb-1 transition-colors border-b-2" 
                            :class="activeView === 'contacts' ? 'text-green-600 dark:text-green-400 border-green-500' : 'text-gray-400 border-transparent hover:text-gray-600 dark:hover:text-gray-300'">
                        Contacts
                    </button>
                </div>

                <!-- Header: Active Chat view -->
                <div x-show="activeView === 'chat'" class="flex items-center gap-2 flex-1 min-w-0">
                    <button @click="showInbox()" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full text-gray-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <img :src="chatUser?.avatar" class="w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-600">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-gray-800 dark:text-gray-100 truncate" x-text="chatUser?.name"></p>
                        <p class="text-[9px] text-green-500 font-semibold leading-none mt-0.5">Online</p>
                    </div>
                </div>

                <!-- Close button -->
                <button @click="closeWidget()" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Panel Body -->
            <div class="flex-1 flex flex-col min-h-0 bg-white dark:bg-gray-800 transition-colors">
                
                <!-- 1. Inbox View -->
                <div x-show="activeView === 'inbox'" class="flex-1 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-700/40">
                    <template x-if="conversations.length === 0">
                        <div class="p-8 text-center text-gray-400 dark:text-gray-500">
                            <p class="text-sm">No active chats.</p>
                            <p class="text-xs mt-1">Start a conversation from your Contacts!</p>
                        </div>
                    </template>
                    <template x-for="c in conversations" :key="c.id">
                        <div @click="openChat(c)" class="p-3.5 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer transition-colors">
                            <div class="relative flex-shrink-0">
                                <img :src="c.avatar" class="w-10 h-10 rounded-full object-cover border border-gray-100 dark:border-gray-600">
                                <span x-show="c.unread_count > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold px-1 py-0.5 rounded-full min-w-[16px] text-center border border-white dark:border-gray-850">
                                    <span x-text="c.unread_count"></span>
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate" x-text="c.name"></span>
                                    <span class="text-[9px] text-gray-400 dark:text-gray-500" x-text="c.last_message ? c.last_message.time : ''"></span>
                                </div>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5" x-text="c.last_message ? c.last_message.body : ''"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- 2. Contacts View -->
                <div x-show="activeView === 'contacts'" class="flex-1 overflow-y-auto p-2 space-y-1">
                    <template x-if="contacts.length === 0">
                        <div class="p-8 text-center text-gray-400 dark:text-gray-500">
                            <p class="text-sm">No contacts found.</p>
                            <p class="text-xs mt-1">Follow users in Search to start chatting!</p>
                        </div>
                    </template>
                    <template x-for="contact in contacts" :key="contact.id">
                        <div @click="openChat(contact)" class="p-2.5 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-xl cursor-pointer transition-all duration-200">
                            <img :src="contact.avatar" class="w-9 h-9 rounded-full object-cover border border-gray-100 dark:border-gray-600">
                            <span class="text-xs font-semibold text-gray-800 dark:text-gray-200" x-text="contact.name"></span>
                        </div>
                    </template>
                </div>

                <!-- 3. Active Chat View -->
                <div x-show="activeView === 'chat'" class="flex-1 flex flex-col min-h-0 bg-gray-50 dark:bg-gray-900/10">
                    <!-- Message Feed -->
                    <div x-ref="messageContainer" class="flex-1 overflow-y-auto p-4 space-y-3 scrollbar-thin">
                        <template x-for="msg in messages" :key="msg.id">
                            <div class="flex items-start gap-2" :class="msg.is_sent_by_me ? 'justify-end' : 'justify-start'">
                                <!-- Sender Avatar -->
                                <template x-if="!msg.is_sent_by_me">
                                    <img :src="chatUser?.avatar" class="w-6 h-6 rounded-full object-cover mt-1 border border-gray-100 dark:border-gray-700">
                                </template>
                                
                                <!-- Bubble content -->
                                <div class="flex flex-col max-w-[75%]" :class="msg.is_sent_by_me ? 'items-end' : 'items-start'">
                                    <div class="px-3.5 py-2 text-xs shadow-sm"
                                         :class="msg.is_sent_by_me 
                                             ? 'bg-green-600 text-white rounded-2xl rounded-tr-none' 
                                             : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700/60 rounded-2xl rounded-tl-none'">
                                        <p class="leading-relaxed whitespace-pre-wrap break-words" x-text="msg.body"></p>
                                    </div>
                                    <span class="text-[8px] text-gray-400 dark:text-gray-500 mt-1" x-text="msg.time"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Message Form -->
                    <div class="p-3 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex gap-2 transition-colors">
                        <input type="text" 
                               x-model="newMessageText" 
                               @keyup.enter="sendMessage()"
                               placeholder="Write a message..."
                               class="flex-1 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-full px-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500 transition-colors">
                        <button @click="sendMessage()" 
                                class="p-2 rounded-full bg-green-600 hover:bg-green-700 text-white transition-colors flex-shrink-0 flex items-center justify-center w-8 h-8 shadow-md">
                            <svg class="w-3.5 h-3.5 transform rotate-45 -translate-x-[1px]" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                            </svg>
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </div>
    @endauth

    @auth
    <!-- Toast Container for Real-time Notifications -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 space-y-3 pointer-events-none"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let knownNotificationIds = null;

        function checkNotifications() {
            fetch('/api/notifications/unread')
                .then(res => res.json())
                .then(notifications => {
                    // On first load, just remember the existing unread notifications without toasting them
                    if (knownNotificationIds === null) {
                        knownNotificationIds = new Set(notifications.map(n => n.id));
                        return;
                    }

                    // Toast any new notifications
                    notifications.forEach(notification => {
                        if (!knownNotificationIds.has(notification.id)) {
                            knownNotificationIds.add(notification.id);
                            showNotificationToast(notification);
                            
                            // Also dynamically update the navbar badge if it exists
                            const badge = document.querySelector('a[href*="/notifications"] span.absolute');
                            if (badge) {
                                // Extract current count or show badge
                                let currentCount = parseInt(badge.textContent.trim()) || 0;
                                badge.textContent = currentCount + 1;
                                badge.classList.remove('hidden');
                            } else {
                                // If badge didn't exist, we can reload or add a red dot
                                const navLink = document.querySelector('a[href*="/notifications"]');
                                if (navLink) {
                                    const dot = document.createElement('span');
                                    dot.className = "absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full min-w-[16px] h-4 flex items-center justify-center border border-white dark:border-gray-800 shadow-sm leading-none";
                                    dot.textContent = "1";
                                    navLink.appendChild(dot);
                                }
                            }
                        }
                    });
                })
                .catch(err => console.error("Error checking notifications:", err));
        }

        function showNotificationToast(notification) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = "flex items-center gap-3 p-4 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 pointer-events-auto transform translate-y-10 opacity-0 transition-all duration-500 max-w-sm w-80";
            
            toast.innerHTML = `
                <img src="${notification.performer_avatar}" class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-600">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-green-600 dark:text-green-400">New Notification</p>
                    <p class="text-[13px] font-bold truncate">${notification.performer_name}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">${notification.message}</p>
                </div>
            `;

            container.appendChild(toast);

            // Animate-in
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-10', 'opacity-0');
            });

            // Animate-out and remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('translate-y-[-10px]', 'opacity-0');
                setTimeout(() => toast.remove(), 500);
            }, 5000);
        }

        // Start polling every 5 seconds
        checkNotifications();
        setInterval(checkNotifications, 5000);
    });
    </script>
    @endauth

    @stack('scripts')
</body>
</html>
