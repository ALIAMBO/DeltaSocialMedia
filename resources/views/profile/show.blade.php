@extends('layouts.app')
@section('title', $user->name . " Profile")

<!-- Profile upload functions - defined inline so onchange handler can access them -->
<script>
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const MAX_FILE_SIZE_MB = 5;

function validateFileSize(file) {
    if (file.size > MAX_FILE_SIZE) {
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        alert(`⚠️ File size too large!\n\n"${file.name}" is ${fileSizeMB}MB.\n\nMaximum allowed: ${MAX_FILE_SIZE_MB}MB`);
        return false;
    }
    return true;
}

function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file size before preview
    if (!validateFileSize(file)) {
        event.target.value = ''; // Clear the input
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('preview-img').src = e.target.result;
        document.getElementById('image-preview').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}
</script>

@php
$avatarUrl = $user->profile?->avatar_url ?? asset('images/default-avatar.png');
$coverUrl = $user->profile?->cover_photo ? $user->profile->cover_url : null;
$avatarUrlJson = json_encode($avatarUrl);
$coverUrlJson = json_encode($coverUrl);
@endphp

@section('content')
<div class="space-y-5" x-data="{ isOpen: false, imageSrc: '', imageTitle: '', avatarUrl: {{ $avatarUrlJson }}, coverUrl: {{ $coverUrlJson }}, profileOpen: false, openModal(src, title) { this.imageSrc = src; this.imageTitle = title; this.isOpen = true; document.body.style.overflow = 'hidden'; }, closeModal() { this.isOpen = false; document.body.style.overflow = 'auto'; } }">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="relative h-44 bg-gradient-to-r from-green-400 to-green-500 rounded-t-2xl overflow-hidden {{ $coverUrl ? 'cursor-pointer group' : '' }}" @click="coverUrl ? openModal(coverUrl, 'Cover Photo') : null">
            @if ($coverUrl)
                <img src="{{ $coverUrl }}" alt="Cover Photo" class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
            @endif
            @if ($coverUrl)
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition flex items-center justify-center pointer-events-none"></div>
            @endif
        </div>
        <div class="pt-16 px-6 pb-5 relative" style="margin-top: -50px;">
            <img @click="openModal(avatarUrl, 'Profile Picture')" src="{{ $avatarUrl }}" class="w-24 h-24 rounded-full border-4 border-white mb-10 cursor-pointer hover:opacity-75 transition-opacity" alt="avatar">
            <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
            @if ($user->profile?->bio)
                <p class="text-gray-600 mt-1">{{ $user->profile->bio }}</p>
            @endif
            <div class="flex gap-6 mt-4 text-sm">
                <span><strong>{{ $user->posts->count() }}</strong> Posts</span>
                <span><strong>{{ $user->followers->count() }}</strong> Followers</span>
                <span><strong>{{ $user->following->count() }}</strong> Following</span>
            </div>
            <div class="flex gap-2 mt-4">
                @if (auth()->id() !== $user->id)
                    <form action="{{ route('follow.toggle', $user) }}" method="POST" class="inline">
                        @csrf
                        <button class="px-5 py-2 rounded-full text-sm font-semibold bg-green-600 text-white">
                            {{ $isFollowing ? 'Unfollow' : 'Follow' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('profile.edit') }}" class="px-5 py-2 rounded-full text-sm font-semibold border">Edit Profile</a>
                @endif
            </div>
        </div>
    </div>

    <!-- Create Post (Only on your own profile) -->
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
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 resize-none">{{ old('body') }}</textarea>

                    @error('body')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center justify-between mt-2">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-500 hover:text-green-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Photo</span>
                            <input type="file" name="image" accept="image/*" class="hidden" onchange="previewImage(event)">
                        </label>
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-5 py-2 rounded-full transition">
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

    <!-- User's Posts -->
    @if ($user->posts->isEmpty())
        <div class="bg-white rounded-2xl p-8 text-center text-gray-400">No posts yet.</div>
    @else
        <div class="space-y-4">
            @foreach ($user->posts as $post)
                @include('components.post-card', ['post' => $post])
            @endforeach
        </div>
    @endif
    
    <!-- Image Modal -->
    <template x-if="isOpen" @keydown.escape.window="closeModal()">
        <div class="fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center p-4" @click="closeModal()">
            <div class="relative max-w-4xl w-full max-h-[90vh]" @click.stop>
                <button @click="closeModal()" class="absolute top-4 right-4 z-20 bg-white/20 hover:bg-white/40 text-white p-2 rounded-full transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="flex items-center justify-center h-[90vh] overflow-auto bg-black rounded-lg">
                    <img :src="imageSrc" :alt="imageTitle" class="max-w-full max-h-full object-contain" @click.stop>
                </div>
            </div>
        </div>
    </template>
</div>

@endsection
