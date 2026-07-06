@extends('layouts.app')
@section('title', $user->name . " Profile")

@section('content')


@php
$avatarUrl = $user->profile?->avatar_url ?? asset('images/default-avatar.png');
$coverUrl = $user->profile?->cover_photo ? $user->profile->cover_url : null;
$avatarUrlJson = json_encode($avatarUrl);
$coverUrlJson = json_encode($coverUrl);
@endphp


<div class="space-y-5 bg-transparent min-h-screen transition-colors" x-data="{
    isOpen: false,
    imageSrc: '',
    imageTitle: '',
    avatarUrl: {{ $avatarUrlJson }},
    coverUrl: {{ $coverUrlJson }},
    profileOpen: false,
    openModal(src, title) {
        this.imageSrc = src;
        this.imageTitle = title;
        this.isOpen = true;
        document.body.style.overflow = 'hidden';
    },
    closeModal() {
        this.isOpen = false;
        document.body.style.overflow = 'auto';
    },
    
    // Follow list states
    showFollowList: false,
    followListType: '',
    followListUsers: [],
    followListLoading: false,
    
    async openFollowList(type) {
        this.followListType = type;
        this.showFollowList = true;
        this.followListLoading = true;
        this.followListUsers = [];
        document.body.style.overflow = 'hidden';
        
        try {
            const response = await fetch('/' + '@' + '{{ $user->getRouteKey() }}/' + type);
            if (response.ok) {
                this.followListUsers = await response.json();
            }
        } catch (e) {
            console.error(e);
        } finally {
            this.followListLoading = false;
        }
    },
    closeFollowList() {
        this.showFollowList = false;
        document.body.style.overflow = 'auto';
    },
    async toggleFollowInList(user) {
        try {
            const response = await fetch('/@' + user.slug + '/follow', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                const data = await response.json();
                user.is_following = data.following;
                
                // Also update main follow button if target is current profile owner
                if (user.id === {{ $user->id }}) {
                    const mainFollowBtn = document.querySelector('.follow-btn');
                    if (mainFollowBtn) {
                        mainFollowBtn.textContent = user.is_following ? 'Unfollow' : 'Follow';
                    }
                }
                
                // Update follower counts on elements
                const followerEl = document.querySelector('[data-followers-count-for=&quot;' + user.id + '&quot;]');
                if (followerEl) {
                    followerEl.textContent = data.followers_count;
                }
            }
        } catch (e) {
            console.error(e);
        }
    }
}">
    <x-liquid-glass-card>
        <div class="relative h-44 bg-gradient-to-r from-green-400 to-green-500 rounded-t-2xl overflow-hidden {{ $coverUrl ? 'cursor-pointer group' : '' }}" @click="coverUrl ? openModal(coverUrl, 'Cover Photo') : null">
            @if ($coverUrl)
                <img src="{{ $coverUrl }}" alt="Cover Photo" class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
            @endif
            @if ($coverUrl)
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition flex items-center justify-center pointer-events-none"></div>
            @endif
        </div>
        <div class="pt-16 px-6 pb-5 relative" style="margin-top: -50px;">
            <img @click="openModal(avatarUrl, 'Profile Picture')" src="{{ $avatarUrl }}" class="w-24 h-24 rounded-full border-4 border-white dark:border-gray-800 mb-10 cursor-pointer hover:opacity-75 transition-opacity" alt="avatar">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
            @if ($user->profile?->bio)
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $user->profile->bio }}</p>
            @endif

            @if ($user->profile?->location || $user->profile?->website)
                <div class="flex flex-wrap gap-4 mt-2.5 text-xs text-gray-500 dark:text-gray-400">
                    @if ($user->profile->location)
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-gray-450 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1115 0z"/>
                            </svg>
                            <span>{{ $user->profile->location }}</span>
                        </div>
                    @endif
                    @if ($user->profile->website)
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-gray-450 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                            </svg>
                            <a href="{{ str_starts_with($user->profile->website, 'http') ? $user->profile->website : 'https://' . $user->profile->website }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="text-green-600 dark:text-green-400 hover:underline">
                                {{ preg_replace('/(^https?:\/\/)?(www\.)?/', '', $user->profile->website) }}
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <div class="flex gap-6 mt-4 text-sm text-gray-700 dark:text-gray-300">
                <span><strong>{{ $user->posts->count() }}</strong> Posts</span>
                <button type="button" @click="openFollowList('followers')" class="hover:underline focus:outline-none">
                    <strong data-followers-count-for="{{ $user->id }}">{{ $user->followers_count }}</strong> Followers
                </button>
                <button type="button" @click="openFollowList('following')" class="hover:underline focus:outline-none">
                    <strong>{{ $user->following_count }}</strong> Following
                </button>
            </div>
            <div class="flex gap-2 mt-4">
                @if (auth()->id() !== $user->id)
                    <form action="{{ route('follow.toggle', $user) }}" method="POST" class="inline follow-form" data-user-id="{{ $user->id }}">
                        @csrf
                        <button class="px-5 py-2 rounded-full text-sm font-semibold follow-btn bg-green-600 hover:bg-green-700 text-white dark:bg-green-700 dark:hover:bg-green-600 transition-colors">
                            {{ $isFollowing ? 'Unfollow' : 'Follow' }}
                        </button>
                    </form>
                    <a href="{{ route('chat.show', $user) }}" class="px-5 py-2 rounded-full text-sm font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Message
                    </a>
                @else
                    <a href="{{ route('profile.edit') }}" class="px-5 py-2 rounded-full text-sm font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Edit Profile</a>
                @endif
            </div>
        </div>
    </x-liquid-glass-card>

    <!-- Create Post (Only on your own profile) -->
    @if (auth()->id() === $user->id)
    <x-liquid-glass-card class="p-4">
        <div class="flex items-start gap-3">
            <img src="{{ auth()->user()->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                 class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-600">
            <div class="flex-1">
                <form id="post-create-form" action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <textarea name="body" rows="3"
                        placeholder="What's on your mind?"
                        class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500 resize-none placeholder-gray-500 dark:placeholder-gray-400 transition-colors">{{ old('body') }}</textarea>

                    @error('body')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center justify-between mt-2">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-500 dark:text-gray-400 hover:text-green-500 dark:hover:text-green-400 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Photo</span>
                            <input type="file" name="image" accept="image/*" class="hidden" onchange="previewImage(event)">
                        </label>
                        <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'bg-green-400 dark:bg-green-800 cursor-not-allowed opacity-70' : 'bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600'"
                            class="flex items-center gap-2 text-white text-sm font-medium px-5 py-2 rounded-full transition-colors">
                            <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="submitting ? 'Posting...' : 'Post'"></span>
                        </button>
                    </div>

                    <!-- Image preview -->
                    <div id="image-preview" class="mt-3 hidden" style="position:relative; overflow:visible;">
                        <div style="aspect-ratio: 1; overflow:hidden; position:relative; background:#09090b; max-width:320px;">
                            <img id="preview-img" src="" style="display:block; width:100%; height:100%; object-cover;">
                        </div>
                        <button type="button" onclick="cancelPostImage()" style="position:absolute; top:8px; right:8px; z-index:10;" class="bg-black/60 hover:bg-black/80 text-white rounded-full p-1.5 transition shadow-md" title="Cancel image">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    @error('image')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </form>
            </div>
        </div>
    </x-liquid-glass-card>
    @endif

    <!-- User's Posts -->
    @if ($user->posts->isEmpty())
        <x-liquid-glass-card class="p-8 text-center text-gray-400 dark:text-gray-500">No posts yet.</x-liquid-glass-card>
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

    <!-- Follow List Modal -->
    <template x-if="showFollowList">
        <div class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4" @click.self="closeFollowList()">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full overflow-hidden transition-colors flex flex-col max-h-[80vh]">
                <!-- Header -->
                <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 capitalize" x-text="followListType"></h3>
                    <button type="button" @click="closeFollowList()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Content / User List -->
                <div class="flex-1 overflow-y-auto p-4 space-y-3 min-h-[250px] bg-gray-50 dark:bg-gray-900/20">
                    <!-- Loading state -->
                    <div x-show="followListLoading" class="flex flex-col items-center justify-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-4 border-green-500 border-t-transparent mb-3"></div>
                        <span class="text-xs text-gray-400">Loading users...</span>
                    </div>

                    <!-- Empty state -->
                    <div x-show="!followListLoading && followListUsers.length === 0" class="text-center py-12 text-gray-450 dark:text-gray-500">
                        No users found.
                    </div>

                    <!-- User rows -->
                    <template x-for="usr in followListUsers" :key="usr.id">
                        <div class="flex items-center justify-between gap-3 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700/30 transition-colors">
                            <a :href="'/@' + usr.slug" class="flex items-center gap-3 flex-1 min-w-0">
                                <img :src="usr.avatar_url" class="w-10 h-10 rounded-full object-cover border border-gray-100 dark:border-gray-700">
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate" x-text="usr.name"></h4>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate" x-text="usr.bio"></p>
                                </div>
                            </a>

                            <!-- Follow button -->
                            <template x-if="!usr.is_self">
                                <button @click="toggleFollowInList(usr)" 
                                        :class="usr.is_following 
                                            ? 'bg-gray-150 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-650 dark:text-gray-200' 
                                            : 'bg-green-600 hover:bg-green-700 text-white dark:bg-green-700 dark:hover:bg-green-600'"
                                        class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors min-w-[85px] text-center">
                                    <span x-text="usr.is_following ? 'Unfollow' : 'Follow'"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>


@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<style>
.cropper-bg { background-image: none !important; background-color: #09090b !important; }
.cropper-view-box { outline: 2px solid #16a34a !important; outline-color: #16a34a !important; }
.cropper-line, .cropper-point { background-color: #16a34a !important; }
</style>
@endpush

@push('scripts')
    <script>
    (function() {
        var postCropper = null;

        function initPostCropper() {
            var img = document.getElementById('preview-img');
            if (!img) return;
            if (postCropper) { postCropper.destroy(); postCropper = null; }
            postCropper = new Cropper(img, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.9,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false
            });
        }

        function resetPostCropper() {
            if (postCropper) { postCropper.destroy(); postCropper = null; }
        }

        window.cancelPostImage = function() {
            resetPostCropper();
            var input = document.querySelector('#post-create-form input[name="image"]');
            if (input) input.value = '';
            var preview = document.getElementById('image-preview');
            if (preview) preview.classList.add('hidden');
            var img = document.getElementById('preview-img');
            if (img) img.removeAttribute('src');
        };

        window.previewImage = function() {};

        document.addEventListener('DOMContentLoaded', function() {
            var input = document.querySelector('#post-create-form input[name="image"]');
            if (!input) return;

            input.addEventListener('change', function(e) {
                var file = e.target.files[0];
                if (!file) return;
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image must be less than 5MB.');
                    e.target.value = '';
                    return;
                }
                var preview = document.getElementById('image-preview');
                var img = document.getElementById('preview-img');
                preview.classList.remove('hidden');
                resetPostCropper();
                img.onload = function() {
                    img.onload = null;
                    initPostCropper();
                };
                img.src = URL.createObjectURL(file);
            });


            var form = document.getElementById('post-create-form');
            if (!form) return;
            form.addEventListener('submit', function(e) {
                if (!postCropper) return;
                e.preventDefault();
                var canvas = postCropper.getCroppedCanvas({ width: 1080, height: 1080 });
                if (canvas) {
                    canvas.toBlob(function(blob) {
                        var file = new File([blob], 'post.jpg', { type: 'image/jpeg' });
                        var dt = new DataTransfer();
                        dt.items.add(file);
                        input.files = dt.files;
                        resetPostCropper();
                        form.submit();
                    }, 'image/jpeg', 0.9);
                } else {
                    resetPostCropper();
                    form.submit();
                }
            });
        });
    })();
    </script>
    @vite('resources/js/pages/post-card.js')
@endpush

@endsection
