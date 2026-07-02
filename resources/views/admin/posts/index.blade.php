@extends('layouts.admin')
@section('title', 'Posts')

@section('content')
<div class="space-y-5">

    {{-- Search --}}
    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search post content…"
               class="flex-1 bg-gray-900 border border-gray-700 text-white text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-500 placeholder-gray-600">
        <button class="bg-green-600 hover:bg-green-700 text-white text-sm px-5 py-2.5 rounded-xl font-semibold transition-colors">Search</button>
        @if(request('search'))
            <a href="{{ route('admin.posts') }}" class="border border-gray-700 text-gray-400 hover:text-white text-sm px-4 py-2.5 rounded-xl transition-colors">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-800 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="text-left px-5 py-3">Author</th>
                    <th class="text-left px-4 py-3">Content</th>
                    <th class="text-center px-4 py-3 hidden md:table-cell">❤️ Likes</th>
                    <th class="text-center px-4 py-3 hidden md:table-cell">💬 Comments</th>
                    <th class="text-center px-4 py-3 hidden lg:table-cell">Posted</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($posts as $post)
                <tr class="hover:bg-gray-800/40 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <img src="{{ $post->user?->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                                 class="w-7 h-7 rounded-full object-cover border border-gray-700 flex-shrink-0">
                            <span class="text-white font-semibold truncate text-xs">{{ $post->user?->name ?? 'Deleted' }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 max-w-xs">
                        <p class="text-gray-400 text-xs line-clamp-2">{{ $post->body ?: '(image only)' }}</p>
                        @if($post->image)
                            <span class="text-[10px] text-blue-400 mt-0.5 block">📎 Has image</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-gray-400 hidden md:table-cell">{{ $post->likes->count() }}</td>
                    <td class="px-4 py-3 text-center text-gray-400 hidden md:table-cell">{{ $post->comments->count() }}</td>
                    <td class="px-4 py-3 text-center text-xs text-gray-600 hidden lg:table-cell">{{ $post->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3 text-right">
                        <form action="{{ route('admin.posts.delete', $post) }}" method="POST"
                              onsubmit="return confirm('Delete this post permanently?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-white border border-red-700/40 hover:border-red-500 px-3 py-1.5 rounded-lg transition-colors">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-gray-600">No posts found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="text-gray-500">
        {{ $posts->links() }}
    </div>
</div>
@endsection
