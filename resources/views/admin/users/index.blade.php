@extends('layouts.admin')
@section('title', 'Users')

@section('content')
<div class="space-y-5">

    {{-- Search --}}
    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by name or email…"
               class="flex-1 bg-gray-900 border border-gray-700 text-white text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-500 placeholder-gray-600">
        <button class="bg-green-600 hover:bg-green-700 text-white text-sm px-5 py-2.5 rounded-xl font-semibold transition-colors">Search</button>
        @if(request('search'))
            <a href="{{ route('admin.users') }}" class="border border-gray-700 text-gray-400 hover:text-white text-sm px-4 py-2.5 rounded-xl transition-colors">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-800 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="text-left px-5 py-3">User</th>
                    <th class="text-left px-4 py-3 hidden md:table-cell">Email</th>
                    <th class="text-center px-4 py-3 hidden lg:table-cell">Posts</th>
                    <th class="text-center px-4 py-3 hidden lg:table-cell">Joined</th>
                    <th class="text-center px-4 py-3">Status</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($users as $u)
                <tr class="hover:bg-gray-800/40 transition-colors {{ $u->trashed() ? 'opacity-50' : '' }}">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $u->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                                 class="w-8 h-8 rounded-full object-cover border border-gray-700">
                            <div>
                                <p class="font-semibold text-white">{{ $u->name }}</p>
                                @if($u->is_admin)
                                    <span class="text-[10px] bg-green-600/20 text-green-400 border border-green-600/30 px-1.5 py-0.5 rounded-full font-bold">Admin</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-400 hidden md:table-cell">{{ $u->email }}</td>
                    <td class="px-4 py-3 text-center text-gray-400 hidden lg:table-cell">{{ $u->posts_count }}</td>
                    <td class="px-4 py-3 text-center text-gray-500 text-xs hidden lg:table-cell">{{ $u->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($u->trashed())
                            <span class="text-xs bg-red-900/30 text-red-400 border border-red-700/40 px-2 py-0.5 rounded-full">Banned</span>
                        @else
                            <span class="text-xs bg-green-900/20 text-green-400 border border-green-700/30 px-2 py-0.5 rounded-full">Active</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2 flex-wrap">
                            {{-- View Profile --}}
                            <a href="{{ route('profile.show', $u->withoutTrashed()->find($u->id) ?? $u) }}"
                               class="text-xs text-gray-400 hover:text-white border border-gray-700 hover:border-gray-500 px-2.5 py-1 rounded-lg transition-colors">
                               View
                            </a>

                            {{-- Ban / Unban --}}
                            @if($u->trashed())
                                <form action="{{ route('admin.users.unban', $u->id) }}" method="POST">
                                    @csrf
                                    <button class="text-xs text-green-400 hover:text-white border border-green-700/40 hover:border-green-500 px-2.5 py-1 rounded-lg transition-colors">Unban</button>
                                </form>
                            @elseif($u->id !== auth()->id() && !$u->is_admin)
                                <form action="{{ route('admin.users.ban', $u) }}" method="POST" onsubmit="confirmAction(event, 'Ban {{ addslashes($u->name) }}?')">
                                    @csrf
                                    <button class="text-xs text-red-400 hover:text-white border border-red-700/40 hover:border-red-500 px-2.5 py-1 rounded-lg transition-colors">Ban</button>
                                </form>
                            @endif

                            {{-- Promote / Demote --}}
                            @if(!$u->trashed() && $u->id !== auth()->id())
                                @if($u->is_admin)
                                    <form action="{{ route('admin.users.demote', $u) }}" method="POST">
                                        @csrf
                                        <button class="text-xs text-yellow-400 hover:text-white border border-yellow-700/40 px-2.5 py-1 rounded-lg transition-colors">Demote</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.users.promote', $u) }}" method="POST">
                                        @csrf
                                        <button class="text-xs text-blue-400 hover:text-white border border-blue-700/40 px-2.5 py-1 rounded-lg transition-colors">Make Admin</button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-gray-600">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="text-gray-500">
        {{ $users->links() }}
    </div>
</div>
@endsection
