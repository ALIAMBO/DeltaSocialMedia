<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Post Header -->
    <div class="flex items-center justify-between p-4">
        <a href="{{ route('profile.show', $post->user) }}" class="flex items-center gap-3">
            <img src="{{ $post->user->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                 class="w-10 h-10 rounded-full object-cover border border-gray-200" alt="avatar">
            <div>
                <p class="text-sm font-semibold text-gray-800 hover:text-blue-600">{{ $post->user->name }}</p>
                <p class="text-xs text-gray-400">{{ $post->created_at->diffForHumans() }}</p>
            </div>
        </a>

        @if ($post->user_id === auth()->id())
        <form action="{{ route('posts.destroy', $post) }}" method="POST"
              onsubmit="return confirm('Delete this post?')">
            @csrf @method('DELETE')
            <button class="text-gray-400 hover:text-red-500 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </form>
        @endif
    </div>

    <!-- Post Body -->
    @if ($post->body)
        <p class="px-4 pb-3 text-sm text-gray-700 leading-relaxed">{{ $post->body }}</p>
    @endif

    <!-- Post Image -->
    @if ($post->image)
        <img src="{{ $post->image_url }}" alt="Post image" 
             onclick="openImageModal{{ $post->id }}()" 
             class="w-full object-cover max-h-96 cursor-pointer hover:opacity-90 transition-opacity">
        
        <!-- Image Modal -->
        <div id="imageModal{{ $post->id }}" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4"
             onclick="closeImageModal{{ $post->id }}(event)">
            <div class="relative max-w-4xl max-h-[90vh] flex items-center justify-center" onclick="event.stopPropagation()">
                <img src="{{ $post->image_url }}" alt="Full view" class="max-w-full max-h-[90vh] object-contain rounded-lg">
                <button onclick="closeImageModal{{ $post->id }}()"
                        class="absolute top-4 right-4 text-white bg-black bg-opacity-50 hover:bg-opacity-75 rounded-full p-2 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <script>
        function openImageModal{{ $post->id }}() {
            document.getElementById('imageModal{{ $post->id }}').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeImageModal{{ $post->id }}(event) {
            if (event && event.target.id !== 'imageModal{{ $post->id }}') return;
            document.getElementById('imageModal{{ $post->id }}').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageModal{{ $post->id }}();
            }
        });
        </script>
    @endif

    <!-- Like / Comment actions -->
    <div class="px-4 py-3 border-t border-gray-100 flex items-center gap-4">
        <!-- Like -->
        <form action="{{ route('posts.like', $post) }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center gap-1.5 text-sm font-medium
                {{ $post->isLikedBy(auth()->user()) ? 'text-red-500' : 'text-gray-500 hover:text-red-400' }} transition">
                <svg class="w-5 h-5" fill="{{ $post->isLikedBy(auth()->user()) ? 'currentColor' : 'none' }}"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span>{{ $post->likes->count() }} {{ Str::plural('Like', $post->likes->count()) }}</span>
            </button>
        </form>

        <!-- Comment toggle -->
        <button onclick="toggleComments({{ $post->id }})"
                class="flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-blue-500 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span>{{ $post->comments->count() }} {{ Str::plural('Comment', $post->comments->count()) }}</span>
        </button>
    </div>

    <!-- Comments Section -->
    <div id="comments-{{ $post->id }}" class="hidden border-t border-gray-100 px-4 py-3 space-y-3">
        <!-- Existing comments -->
        @foreach ($post->comments as $comment)
        <div class="flex gap-2">
            <a href="{{ route('profile.show', $comment->user) }}">
                <img src="{{ $comment->user->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                     class="w-7 h-7 rounded-full object-cover border border-gray-200" alt="avatar">
            </a>
            <div class="flex-1 bg-gray-50 rounded-xl px-3 py-2">
                <p class="text-xs font-semibold text-gray-700">{{ $comment->user->name }}</p>
                <p class="text-sm text-gray-600">{{ $comment->body }}</p>
            </div>
            @if ($comment->user_id === auth()->id())
            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="self-center">
                @csrf @method('DELETE')
                <button class="text-gray-300 hover:text-red-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </form>
            @endif
        </div>
        @endforeach

        <!-- Add comment -->
        <form action="{{ route('comments.store', $post) }}" method="POST" class="flex gap-2 mt-1">
            @csrf
            <img src="{{ auth()->user()->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                 class="w-7 h-7 rounded-full object-cover border border-gray-200" alt="avatar">
            <div class="flex-1 flex gap-2">
                <input type="text" name="body" placeholder="Write a comment..."
                       class="flex-1 bg-gray-100 rounded-full px-4 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-4 py-1.5 rounded-full transition">
                    Send
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleComments(postId) {
    const el = document.getElementById('comments-' + postId);
    el.classList.toggle('hidden');
}
</script>
