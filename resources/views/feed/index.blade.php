@extends('layouts.app')
@section('title', 'Feed')

@section('content')
@php
    $storiesJson = $usersWithStories->map(function ($u) {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'avatar' => $u->profile?->avatar_url ?? asset('images/default-avatar.png'),
            'stories' => $u->stories->map(function ($s) {
                return [
                    'id' => $s->id,
                    'image' => $s->image_url,
                    'caption' => $s->caption,
                    'created_at' => $s->created_at->diffForHumans(),
                    'is_owner' => $s->user_id === auth()->id()
                ];
            })->toArray()
        ];
    })->toJson();
@endphp

<!-- Wrapper for Alpine stories feature state -->
<div x-data="{ 
    uploadOpen: false, 
    showStories: false, 
    activeUserIndex: 0, 
    activeStoryIndex: 0, 
    progress: 0, 
    timer: null,
    users: {{ $storiesJson }},
    readStories: JSON.parse(localStorage.getItem('readStories') || '[]'),
    storyHasImg: false,
    storyPreviewSrc: '',
    
    init() {
        this.$watch('uploadOpen', value => {
            if (!value) {
                this.storyHasImg = false;
                this.storyPreviewSrc = '';
                resetCropper();
                const input = document.getElementById('story-image-input');
                if (input) input.value = '';
            }
        });
    },
    
    isUserStoriesUnread(userId) {
        let u = this.users.find(user => user.id === userId);
        if (!u || u.stories.length === 0) return false;
        return u.stories.some(story => !this.readStories.includes(story.id));
    },
    
    markStoryAsRead(storyId) {
        if (!this.readStories.includes(storyId)) {
            this.readStories.push(storyId);
            localStorage.setItem('readStories', JSON.stringify(this.readStories));
        }
    },
    
    markCurrentStoryAsRead() {
        let currentUser = this.users[this.activeUserIndex];
        if (currentUser && currentUser.stories[this.activeStoryIndex]) {
            let storyId = currentUser.stories[this.activeStoryIndex].id;
            this.markStoryAsRead(storyId);
        }
    },
    
    selectUser(idx) {
        this.activeUserIndex = idx;
        this.activeStoryIndex = 0;
        this.showStories = true;
        this.startStory();
    },
    
    startStory() {
        this.progress = 0;
        this.markCurrentStoryAsRead();
        if (this.timer) clearInterval(this.timer);
        this.timer = setInterval(() => {
            if (this.progress < 100) {
                this.progress += 1;
            } else {
                this.nextStory();
            }
        }, 50); // 50ms * 100 = 5000ms (5 seconds per story)
    },
    
    nextStory() {
        let currentUser = this.users[this.activeUserIndex];
        if (!currentUser) return;
        
        if (this.activeStoryIndex < currentUser.stories.length - 1) {
            this.activeStoryIndex++;
            this.startStory();
        } else {
            // No more stories for current user, go to next user
            if (this.activeUserIndex < this.users.length - 1) {
                this.activeUserIndex++;
                this.activeStoryIndex = 0;
                this.startStory();
            } else {
                // No more users, close slideshow
                this.closeStories();
            }
        }
    },
    
    prevStory() {
        if (this.activeStoryIndex > 0) {
            this.activeStoryIndex--;
            this.startStory();
        } else {
            // Try previous user
            if (this.activeUserIndex > 0) {
                this.activeUserIndex--;
                // Go to last story of previous user
                this.activeStoryIndex = this.users[this.activeUserIndex].stories.length - 1;
                this.startStory();
            } else {
                // First story of first user, just restart
                this.startStory();
            }
        }
    },
    
    closeStories() {
        this.showStories = false;
        if (this.timer) clearInterval(this.timer);
    },
    
    deleteActiveStory() {
        let story = this.users[this.activeUserIndex]?.stories[this.activeStoryIndex];
        if (story && story.is_owner) {
            if (confirm('Are you sure you want to delete this story?')) {
                let form = document.getElementById('delete-story-form');
                form.action = '/stories/' + story.id;
                form.submit();
            }
        }
    }
}" @keydown.escape.window="closeStories()" class="w-full">

    <!-- Post image upload & crop — uses CDN Cropper, same as story -->
    <script>
        // Placeholder for inline scripts if needed
        // Cropping is now handled by feed-index.js module
    </script>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Main Feed Column -->
        <div class="lg:col-span-2 space-y-5">

            @if (request()->has('tag'))
                <div class="bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800/60 rounded-2xl p-4 flex items-center justify-between text-green-800 dark:text-green-200 transition-colors">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold">Showing posts matching tag:</span>
                        <span class="bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 px-3 py-1 rounded-full text-xs font-bold font-mono">#{{ request()->input('tag') }}</span>
                    </div>
                    <a href="{{ route('feed') }}" class="text-xs font-semibold text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 transition-colors bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800 px-3.5 py-1.5 rounded-full shadow-sm">
                        Clear filter
                    </a>
                </div>
            @endif

            <!-- Stories Bar -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 transition-colors overflow-hidden">
                <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Stories</h3>
                <div class="flex items-center gap-4 overflow-x-auto pb-1 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">
                    <!-- Add Story Circle -->
                    <div class="flex flex-col items-center flex-shrink-0 cursor-pointer" @click="uploadOpen = true">
                        <div class="relative">
                            <img src="{{ auth()->user()->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                                 class="w-14 h-14 rounded-full object-cover border border-gray-200 dark:border-gray-600 hover:opacity-90 transition-opacity">
                            <span class="absolute bottom-0 right-0 bg-green-600 dark:bg-green-700 text-white rounded-full p-1 border-2 border-white dark:border-gray-800 flex items-center justify-center w-5 h-5 shadow-sm">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/>
                                </svg>
                            </span>
                        </div>
                        <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 mt-1.5">Add Story</span>
                    </div>

                    <!-- Active Stories list -->
                    @foreach ($usersWithStories as $idx => $u)
                        <div class="flex flex-col items-center flex-shrink-0 relative group">
                            <!-- Clickable Circle trigger -->
                            <div class="cursor-pointer flex flex-col items-center" @click="selectUser({{ $idx }})">
                                <div class="p-[2.5px] rounded-full shadow-sm hover:scale-105 transition-transform duration-200"
                                     :class="isUserStoriesUnread({{ $u->id }}) ? 'bg-gradient-to-tr from-green-500 to-emerald-600' : 'bg-gray-300 dark:bg-gray-600'">
                                    <div class="p-[1.5px] bg-white dark:bg-gray-800 rounded-full transition-colors">
                                        <img src="{{ $u->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                                             class="w-12 h-12 rounded-full object-cover border border-transparent">
                                    </div>
                                </div>
                                <span class="text-[10px] font-semibold text-gray-600 dark:text-gray-400 mt-1.5 truncate w-14 text-center">
                                    {{ $u->id === auth()->id() ? 'Your Story' : $u->name }}
                                </span>
                            </div>


                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Create Post -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 transition-colors">
                <div class="flex items-start gap-3">
                    <img src="{{ auth()->user()->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                         class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-600">
                    <div class="flex-1">
                        <form id="post-create-form" action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
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
                                    <input type="file" name="image" accept="image/*" class="hidden">
                                </label>
                                <button type="submit"
                                    class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white text-sm font-medium px-5 py-2 rounded-full transition-colors">
                                    Post
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
            </div>

            <!-- Posts -->
            @forelse ($posts as $post)
                @include('components.post-card', ['post' => $post])
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center text-gray-400 dark:text-gray-500 transition-colors">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-sm">No posts yet. Follow some users to see their posts here!</p>
                </div>
            @endforelse

            <!-- Pagination -->
            <div class="text-gray-500 dark:text-gray-400">{{ $posts->links() }}</div>
        </div>

        <!-- Sidebar Column -->
        <div class="space-y-4">
            <!-- Your card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex items-center gap-3 transition-colors">
                <img src="{{ auth()->user()->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                     class="w-12 h-12 rounded-full object-cover border border-gray-200 dark:border-gray-600" alt="avatar">
                <div>
                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-100">{{ auth()->user()->name }}</p>
                    <a href="{{ route('profile.show', auth()->user()) }}" class="text-xs text-green-500 dark:text-green-400 hover:underline">View profile</a>
                </div>
            </div>

            <!-- Who to follow -->
            @if ($suggestions->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 transition-colors">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Suggested Users</h3>
                <div class="space-y-3">
                    @foreach ($suggestions as $suggestion)
                    <div class="flex items-center justify-between">
                        <a href="{{ route('profile.show', $suggestion) }}" class="flex items-center gap-2">
                            <img src="{{ $suggestion->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                                 class="w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-600" alt="avatar">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition">{{ $suggestion->name }}</span>
                        </a>
                        <form action="{{ route('follow.toggle', $suggestion) }}" method="POST" class="follow-form" data-user-id="{{ $suggestion->id }}">
                            @csrf
                            <button class="text-xs text-green-600 dark:text-green-400 hover:underline font-medium follow-btn">Follow</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- 1. Add Story Modal -->
    <div x-show="uploadOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         x-transition
         x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 border border-gray-100 dark:border-gray-700 shadow-2xl transition-colors" @click.away="uploadOpen = false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Add New Story</h3>
                <button @click="uploadOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="story-form" action="{{ route('stories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <!-- Image Selection & Cropping -->
                    <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center hover:border-green-500 dark:hover:border-green-500 transition-colors relative"
                         id="story-upload-container"
                         style="overflow:visible;">
                        <input type="file" id="story-image-input" name="image" accept="image/*" 
                               class="absolute inset-0 opacity-0 cursor-pointer"
                               :class="storyHasImg ? 'hidden' : ''"
                               required
                               @change="
                                   const file = $event.target.files[0];
                                   if (file) {
                                       if (file.size > 5 * 1024 * 1024) {
                                           alert('Image must be less than 5MB.');
                                           $event.target.value = '';
                                           storyHasImg = false;
                                           return;
                                       }
                                       const reader = new FileReader();
                                       reader.onload = (e) => {
                                           storyPreviewSrc = e.target.result;
                                           storyHasImg = true;
                                           $nextTick(() => {
                                               const img = document.getElementById('cropper-image');
                                               if (!img) return;
                                               if (img.complete && img.naturalWidth > 0) {
                                                   initCropper();
                                               } else {
                                                   img.onload = function() { img.onload = null; initCropper(); };
                                               }
                                           });
                                       };
                                       reader.readAsDataURL(file);
                                   }
                               ">
                        <div x-show="!storyHasImg" class="py-6 pointer-events-none">
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Choose your story image</p>
                            <p class="text-xs text-gray-400 mt-1">Size limit: 5MB</p>
                        </div>
                        <div x-show="storyHasImg" class="relative bg-zinc-950" style="overflow:visible;">
                            <div style="aspect-ratio: 1; overflow:hidden; position:relative; max-width:320px;">
                                <img id="cropper-image" :src="storyPreviewSrc" style="display:block; width:100%; height:100%; object-cover;">
                            </div>
                            <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex items-center gap-2 bg-black/60 backdrop-blur-md px-3 py-1.5 rounded-full z-10">
                                <button type="button" 
                                        @click="
                                            resetCropper();
                                            storyHasImg = false;
                                            storyPreviewSrc = '';
                                            document.getElementById('story-image-input').value = '';
                                        "
                                        class="text-xs text-red-400 hover:text-red-300 font-semibold px-2 py-1 transition-colors">
                                    Remove Image
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Caption Input -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Caption (Optional)</label>
                        <input type="text" name="caption" maxlength="150" placeholder="Write a short caption..."
                               class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500 transition-colors">
                    </div>
                </div>

                <div class="flex gap-3 justify-end mt-6">
                    <button type="button" @click="uploadOpen = false"
                            class="border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-5 py-2 rounded-full text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white px-5 py-2 rounded-full text-sm font-medium transition-colors shadow-md">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Stories Slideshow Viewer Modal -->
    <div x-show="showStories" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/95 backdrop-blur-md"
         x-transition
         x-cloak>
        
        <!-- Viewer Container -->
        <div class="relative w-full max-w-lg h-full md:h-[85vh] md:max-h-[800px] flex flex-col justify-between bg-zinc-950 rounded-none md:rounded-2xl overflow-hidden shadow-2xl border border-white/5" @click.away="closeStories()">
            
            <!-- Tap to navigate overlay controls -->
            <div class="absolute inset-0 z-10 flex">
                <div class="w-1/3 h-full cursor-pointer" @click.stop="prevStory()"></div>
                <div class="w-1/3 h-full" @click.stop="closeStories()"></div>
                <div class="w-1/3 h-full cursor-pointer" @click.stop="nextStory()"></div>
            </div>

            <!-- Top bar container: progress bar, user metadata, close button -->
            <div class="absolute top-0 inset-x-0 p-4 z-20 bg-gradient-to-b from-black/90 to-transparent pointer-events-none">
                <!-- Progress Indicators -->
                <div class="flex gap-1.5 w-full mb-3 pointer-events-auto">
                    <template x-for="(story, idx) in (users[activeUserIndex] ? users[activeUserIndex].stories : [])" :key="story.id">
                        <div class="h-1 bg-white/20 rounded-full flex-1 overflow-hidden">
                            <div class="h-full bg-green-500 transition-all duration-[50ms] ease-linear"
                                 :style="{ width: idx < activeStoryIndex ? '100%' : (idx === activeStoryIndex ? progress + '%' : '0%') }">
                            </div>
                        </div>
                    </template>
                </div>

                <!-- User info & Actions -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 pointer-events-auto">
                        <img :src="users[activeUserIndex]?.avatar" class="w-8 h-8 rounded-full object-cover border border-white/20">
                        <div class="flex flex-col">
                            <span class="text-white text-xs font-bold" x-text="users[activeUserIndex]?.name"></span>
                            <span class="text-white/50 text-[10px]" x-text="users[activeUserIndex]?.stories[activeStoryIndex]?.created_at"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pointer-events-auto">
                        <!-- Delete option for own stories -->
                        <button x-show="users[activeUserIndex]?.stories[activeStoryIndex]?.is_owner"
                                @click.stop="deleteActiveStory()"
                                class="text-red-400 hover:text-red-500 p-1.5 bg-white/10 hover:bg-white/20 rounded-full transition-all relative z-30" 
                                title="Delete this story">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>

                        <!-- Close Button -->
                        <button @click.stop="closeStories()" class="text-white/80 hover:text-white p-1 bg-white/10 hover:bg-white/20 rounded-full transition-all relative z-30">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Story Image display -->
            <div class="flex-1 w-full flex items-center justify-center bg-black">
                <template x-if="users[activeUserIndex]?.stories[activeStoryIndex]">
                    <img :src="users[activeUserIndex].stories[activeStoryIndex].image" 
                         class="w-full h-full max-h-[85vh] md:max-h-full object-contain">
                </template>
            </div>

            <!-- Story Caption at the bottom -->
            <template x-if="users[activeUserIndex]?.stories[activeStoryIndex]?.caption">
                <div class="absolute bottom-0 inset-x-0 p-6 z-20 bg-zinc-950/60 backdrop-blur-md text-center border-t border-white/5">
                    <p class="text-white text-sm font-medium tracking-wide" 
                       x-text="users[activeUserIndex].stories[activeStoryIndex].caption"></p>
                </div>
            </template>
        </div>
    </div>

    <!-- Hidden form for deleting stories -->
    <form id="delete-story-form" action="" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

</div>
@endsection

@push('styles')
<style>
.cropper-bg {
    background-image: none !important;
    background-color: #09090b !important;
}
.cropper-view-box {
    outline: 2px solid #16a34a !important;
    outline-color: #16a34a !important;
}
.cropper-line, .cropper-point {
    background-color: #16a34a !important;
}
</style>
@endpush

@push('scripts')
    @vite(['resources/js/pages/feed-index.js', 'resources/js/pages/post-card.js'])
@endpush
