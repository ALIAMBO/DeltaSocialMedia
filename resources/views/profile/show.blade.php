@extends('layouts.app')
@section('title', $user->name . " Profile")

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
