@extends('layouts.admin')
@section('title', 'Stories')

@section('content')
<div class="space-y-5">

    {{-- Grid of stories --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
        @forelse($stories as $story)
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden group relative">
            {{-- Story Image --}}
            <div class="aspect-[9/16] bg-gray-950 relative overflow-hidden">
                @if($story->image)
                    <img src="{{ Storage::url('stories/' . $story->image) }}"
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-700 text-xs">No image</div>
                @endif

                {{-- Expiry badge --}}
                <div class="absolute top-2 left-2">
                    @if($story->created_at->diffInHours(now()) < 24)
                        <span class="text-[9px] bg-green-600/80 text-white px-1.5 py-0.5 rounded-full font-bold backdrop-blur">Active</span>
                    @else
                        <span class="text-[9px] bg-gray-700/80 text-gray-300 px-1.5 py-0.5 rounded-full font-bold backdrop-blur">Expired</span>
                    @endif
                </div>
            </div>

            {{-- Info --}}
            <div class="p-3">
                <div class="flex items-center gap-2 mb-2">
                    <img src="{{ $story->user?->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                         class="w-5 h-5 rounded-full object-cover border border-gray-700">
                    <span class="text-xs text-white font-semibold truncate">{{ $story->user?->name }}</span>
                </div>
                @if($story->caption)
                    <p class="text-[10px] text-gray-500 line-clamp-2 mb-2">{{ $story->caption }}</p>
                @endif
                <p class="text-[10px] text-gray-600">{{ $story->created_at->diffForHumans() }}</p>
            </div>

            {{-- Delete action --}}
            <div class="px-3 pb-3">
                <form action="{{ route('admin.stories.delete', $story) }}" method="POST"
                      onsubmit="return confirm('Delete this story?')">
                    @csrf @method('DELETE')
                    <button class="w-full text-xs text-red-400 hover:text-white border border-red-700/40 hover:border-red-500 py-1.5 rounded-lg transition-colors">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center text-gray-600">No stories found.</div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="text-gray-500">
        {{ $stories->links() }}
    </div>
</div>
@endsection
