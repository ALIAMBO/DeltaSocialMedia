@extends('layouts.app')
@section('title', $user->name . "'s Profile")

@section('content')
<div class="space-y-5">

    <!-- Cover & Avatar Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">

        <!-- Cover Photo -->
        <div class="relative">
            {{-- Cover strip with rounded top --}}
            <div class="h-44 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-t-2xl overflow-hidden">
                <img src="{{ $user->profile?->cover_url ?? '' }}"
                     alt="Cover"
                     class="w-full h-full object-cover {{ $user->profile?->cover_photo ? '' : 'hidden' }}">
            </div>

            {{-- Avatar: absolute bottom of cover, shifted up by half its own height (48px = half of 96px / w-24) --}}
            <div class="absolute left-10" style="bottom: -4px;">
                <img src="{{ $user->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                     class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md"
                     alt="avatar">
            </div>
        </div>

        <!-- Info: padding-top must be >= half avatar height (48px) + some breathing room -->
        <div class="pt-16 px-6 pb-5">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ $user->name }}</h1>

                    @if ($user->profile?->bio)
                        <p class="text-sm text-gray-600 mt-1">{{ $user->profile->bio }}</p>
                    @endif

                    <div class="flex flex-wrap gap-4 mt-2 text-sm text-gray-500">
                        @if ($user->profile?->location)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                {{ $user->profile->location }}
                            </span>
                        @endif
                        @if ($user->profile?->website)
                            <a href="{{ $user->profile->website }}" target="_blank"
                               class="flex items-center gap-1 text-blue-500 hover:underline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                {{ $user->profile->website }}
                            </a>
                        @endif
                    </div>

                    <!-- Stats -->
                    <div class="flex gap-6 mt-3 text-sm font-medium text-gray-700">
                        <span><strong class="text-gray-900">{{ $user->posts->count() }}</strong> Posts</span>
                        <span><strong class="text-gray-900">{{ $user->followers->count() }}</strong> Followers</span>
                        <span><strong class="text-gray-900">{{ $user->following->count() }}</strong> Following</span>
                    </div>
                </div>
                

                <!-- Action buttons -->
                <div class="flex gap-2 mt-1 flex-shrink-0">
                    @if (auth()->id() !== $user->id)
                        <form action="{{ route('follow.toggle', $user) }}" method="POST">
                            @csrf
                            <button class="{{ $isFollowing
                                    ? 'bg-gray-200 text-gray-700 hover:bg-red-100 hover:text-red-600'
                                    : 'bg-blue-600 text-white hover:bg-blue-700' }}
                                    px-5 py-2 rounded-full text-sm font-semibold transition">
                                {{ $isFollowing ? 'Unfollow' : 'Follow' }}
                            </button>
                        </form>
                        <a href="{{ route('chat.show', $user) }}"
                           class="px-5 py-2 rounded-full text-sm font-semibold border border-gray-300 hover:bg-gray-50 transition">
                            Message
                        </a>
                    @else
                        <a href="{{ route('profile.edit') }}"
                           class="px-5 py-2 rounded-full text-sm font-semibold border border-gray-300 hover:bg-gray-50 transition">
                            Edit Profile
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Create Post -->
    @if (auth()->id() === $user->id)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-start gap-3">
                <img src="{{ auth()->user()->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                     class="w-10 h-10 rounded-full object-cover border border-gray-200" alt="avatar">
                <div class="flex-1">
                    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <textarea name="body" rows="3"
                            placeholder="What's on your mind?"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('body') }}</textarea>

                        @error('body')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror

                        <div class="flex items-center justify-between mt-2">
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-500 hover:text-blue-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Photo</span>
                                <input type="file" name="image" accept="image/*" class="hidden" onchange="previewImage(event)">
                            </label>
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-full transition">
                                Post
                            </button>
                        </div>

                        <!-- Image preview -->
                        <div id="image-preview" class="mt-2 hidden">
                            <img id="preview-img" src="" alt="Preview" class="rounded-xl max-h-48 object-cover">
                        </div>

                        @error('image')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </form>
                </div>
            </div>
        </div>
        @endif

    <!-- Posts -->
    <div>
        <h2 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Posts</h2>
        @if ($user->posts->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center text-gray-400 text-sm">
                No posts yet.
            </div>
        @else
            <div class="space-y-4">
                @foreach ($user->posts as $post)
                    @include('components.post-card', ['post' => $post])
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('preview-img').src = e.target.result;
        document.getElementById('image-preview').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}
</script>
@endpush
