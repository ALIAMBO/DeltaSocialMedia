@extends('layouts.app')
@section('title', 'Feed')

@section('content')
<!-- Feed upload functions - defined inline so onchange handler can access them -->
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Main Feed Column -->
    <div class="lg:col-span-2 space-y-5">

       <!-- Create Post -->
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

        <!-- Posts -->
        @forelse ($posts as $post)
            @include('components.post-card', ['post' => $post])
        @empty
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="text-sm">No posts yet. Follow some users to see their posts here!</p>
            </div>
        @endforelse

        <!-- Pagination -->
        <div>{{ $posts->links() }}</div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-4">
        <!-- Your card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <img src="{{ auth()->user()->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                 class="w-12 h-12 rounded-full object-cover border border-gray-200" alt="avatar">
            <div>
                <p class="font-semibold text-sm text-gray-800">{{ auth()->user()->name }}</p>
                <a href="{{ route('profile.show', auth()->user()) }}" class="text-xs text-green-500 hover:underline">View profile</a>
            </div>
        </div>

        <!-- Who to follow -->
        @php
            $suggestions = \App\Models\User::where('id', '!=', auth()->id())
                ->whereNotIn('id', auth()->user()->following()->pluck('following_id'))
                ->with('profile')
                ->inRandomOrder()
                ->limit(5)
                ->get();
        @endphp

        @if ($suggestions->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Suggested Users</h3>
            <div class="space-y-3">
                @foreach ($suggestions as $suggestion)
                <div class="flex items-center justify-between">
                    <a href="{{ route('profile.show', $suggestion) }}" class="flex items-center gap-2">
                        <img src="{{ $suggestion->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                             class="w-8 h-8 rounded-full object-cover border border-gray-200" alt="avatar">
                        <span class="text-sm font-medium text-gray-700 hover:text-green-600">{{ $suggestion->name }}</span>
                    </a>
                    <form action="{{ route('follow.toggle', $suggestion) }}" method="POST">
                        @csrf
                        <button class="text-xs text-green-600 hover:underline font-medium">Follow</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/post-card.js')
@endpush
