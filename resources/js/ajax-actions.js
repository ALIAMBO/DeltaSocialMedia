/**
 * Global AJAX Actions Handlers
 * 
 * Intercepts form submissions for Follow, Like, and Comment operations
 * to process them asynchronously via Fetch API and update the DOM dynamically
 * with transitions and micro-animations.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Global Event Listener for form submissions
    document.addEventListener('submit', function(event) {
        const form = event.target;
        
        // 1. Like Form Toggle
        if (form.classList.contains('like-form')) {
            event.preventDefault();
            handleLikeSubmit(form);
        }
        
        // 2. Comment Add Form
        if (form.classList.contains('comment-store-form')) {
            event.preventDefault();
            handleCommentStoreSubmit(form);
        }
        
        // 3. Comment Delete Form
        if (form.classList.contains('comment-delete-form')) {
            event.preventDefault();
            handleCommentDeleteSubmit(form);
        }
        
        // 4. Follow / Unfollow Toggle Form
        if (form.classList.contains('follow-form')) {
            event.preventDefault();
            handleFollowSubmit(form);
        }
    });
});

/**
 * Handle Like submission via AJAX
 */
function handleLikeSubmit(form) {
    const button = form.querySelector('button');
    if (!button) return;
    
    button.disabled = true;
    
    const url = form.getAttribute('action');
    const csrfToken = form.querySelector('input[name="_token"]').value;
    const postId = form.getAttribute('data-post-id');
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({})
    })
    .then(response => {
        if (!response.ok) throw new Error('Like operation failed');
        return response.json();
    })
    .then(data => {
        button.disabled = false;
        
        // Sync all like buttons for this post on the page
        const allLikeForms = document.querySelectorAll(`.like-form[data-post-id="${postId}"]`);
        allLikeForms.forEach(f => {
            const btn = f.querySelector('button');
            const svg = btn.querySelector('svg');
            const span = btn.querySelector('.like-count');
            
            if (data.liked) {
                btn.className = 'flex items-center gap-1.5 text-sm font-medium text-green-600 dark:text-green-400 hover:text-green-500 dark:hover:text-green-400 transition transform scale-110 duration-200';
                if (svg) svg.setAttribute('fill', 'currentColor');
            } else {
                btn.className = 'flex items-center gap-1.5 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-green-500 dark:hover:text-green-400 transition transform scale-95 duration-200';
                if (svg) svg.setAttribute('fill', 'none');
            }
            
            if (span) {
                span.textContent = `${data.likes_count} ${data.likes_count === 1 ? 'Like' : 'Likes'}`;
            }
            
            // Return button scale to normal
            setTimeout(() => {
                btn.classList.remove('scale-110', 'scale-95');
            }, 200);
        });
    })
    .catch(error => {
        button.disabled = false;
        console.error('Error toggling like:', error);
    });
}

/**
 * Handle Comment creation via AJAX
 */
function handleCommentStoreSubmit(form) {
    const input = form.querySelector('input[name="body"]');
    const button = form.querySelector('button[type="submit"]');
    const parentIdInput = form.querySelector('input[name="parent_id"]');
    if (!input || !button) return;
    
    const bodyText = input.value.trim();
    if (!bodyText) return;
    
    input.disabled = true;
    button.disabled = true;
    
    const url = form.getAttribute('action');
    const csrfToken = form.querySelector('input[name="_token"]').value;
    const postId = form.getAttribute('data-post-id');
    
    const payload = { body: bodyText };
    if (parentIdInput) {
        payload.parent_id = parentIdInput.value;
    }
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        if (!response.ok) throw new Error('Comment addition failed');
        return response.json();
    })
    .then(data => {
        input.disabled = false;
        button.disabled = false;
        input.value = ''; // clear input field
        
        // Add comment dynamically
        const commentSection = document.getElementById(`comments-${postId}`);
        if (commentSection) {
            let containerToAppend;
            if (data.comment.parent_id) {
                // It's a reply, append to the replies container of the parent comment
                containerToAppend = document.getElementById(`replies-list-${data.comment.parent_id}`);
                // Hide the reply form after successful reply submission
                const replyForm = document.getElementById(`reply-form-${data.comment.parent_id}`);
                if (replyForm) {
                    replyForm.classList.add('hidden');
                }
            } else {
                // It's a top-level comment, append to the main comment list
                containerToAppend = commentSection.querySelector('.comment-list');
            }

            if (containerToAppend) {
                const comment = data.comment;
                const isReply = !!data.comment.parent_id;
                const avatarSize = isReply ? 'w-6 h-6' : 'w-7 h-7';
                
                const commentHtml = `
                    <div class="${isReply ? 'flex gap-2' : 'flex flex-col gap-2'} comment-item opacity-0 translate-y-2 transition-all duration-300 ease-out" id="comment-${comment.id}">
                        <div class="flex gap-2">
                            <a href="${comment.profile_url}">
                                <img src="${comment.avatar_url}"
                                     class="${avatarSize} rounded-full object-cover border border-gray-200 dark:border-gray-600" alt="avatar">
                            </a>
                            <div class="flex-1 bg-gray-50 dark:bg-gray-700 rounded-xl px-3 py-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">${comment.user_name}</p>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">${comment.created_at_diff}</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">${comment.body}</p>
                                <div class="mt-1 flex items-center gap-3 text-xs text-gray-500">
                                    ${!isReply ? `<button type="button" onclick="document.getElementById('reply-form-${comment.id}').classList.toggle('hidden')" class="hover:text-blue-500 font-medium transition">Reply</button>` : ''}
                                    <form action="${comment.delete_url}" method="POST" class="inline comment-delete-form" data-comment-id="${comment.id}" data-post-id="${postId}">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button class="hover:text-red-500 font-medium transition">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        ${!isReply ? `
                        <div class="ml-9 space-y-2 replies-list" id="replies-list-${comment.id}"></div>
                        <form action="${url}" method="POST" id="reply-form-${comment.id}" class="hidden ml-9 flex gap-2 mt-1 comment-store-form" data-post-id="${postId}">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="parent_id" value="${comment.id}">
                            <img src="${document.querySelector('#post-create-form img')?.src || comment.avatar_url}"
                                 class="w-6 h-6 rounded-full object-cover border border-gray-200 dark:border-gray-600" alt="avatar">
                            <div class="flex-1 flex gap-2">
                                <input type="text" name="body" placeholder="Reply to ${comment.user_name}..." required
                                       class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-full px-3 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-500 placeholder-gray-500 dark:placeholder-gray-400 transition-colors">
                                <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white text-[10px] font-medium px-3 py-1 rounded-full transition-colors">
                                    Reply
                                </button>
                            </div>
                        </form>
                        ` : ''}
                    </div>
                `;
                
                containerToAppend.insertAdjacentHTML('beforeend', commentHtml);
                const addedItem = document.getElementById(`comment-${comment.id}`);
                // Flush layout buffer to run transition animation
                addedItem.offsetHeight;
                addedItem.classList.remove('opacity-0', 'translate-y-2');
            }
        }
        
        // Update all comment counts for this post
        const countLabels = document.querySelectorAll(`.comment-count-label[data-post-id="${postId}"]`);
        countLabels.forEach(el => {
            el.textContent = `${data.comments_count} ${data.comments_count === 1 ? 'Comment' : 'Comments'}`;
        });
    })
    .catch(error => {
        input.disabled = false;
        button.disabled = false;
        console.error('Error adding comment:', error);
    });
}

/**
 * Handle Comment deletion via AJAX
 */
function handleCommentDeleteSubmit(form) {
    Swal.fire({
        title: 'Delete this comment?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#3f3f46',
        confirmButtonText: 'Delete',
        background: document.documentElement.classList.contains('dark') ? '#18181b' : '#ffffff',
        color: document.documentElement.classList.contains('dark') ? '#ffffff' : '#000000',
    }).then((result) => {
        if (!result.isConfirmed) return;
        
        const button = form.querySelector('button');
    if (button) button.disabled = true;
    
    const url = form.getAttribute('action');
    const csrfToken = form.querySelector('input[name="_token"]').value;
    const commentId = form.getAttribute('data-comment-id');
    const postId = form.getAttribute('data-post-id');
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ _method: 'DELETE' })
    })
    .then(response => {
        if (!response.ok) throw new Error('Comment deletion failed');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const commentItem = document.getElementById(`comment-${commentId}`);
            if (commentItem) {
                // Transition exit animation
                commentItem.className = 'flex gap-2 comment-item opacity-0 scale-95 transition-all duration-300 ease-in-out';
                setTimeout(() => {
                    commentItem.remove();
                }, 300);
            }
            
            // Sync all comment count labels
            const countLabels = document.querySelectorAll(`.comment-count-label[data-post-id="${postId}"]`);
            countLabels.forEach(el => {
                el.textContent = `${data.comments_count} ${data.comments_count === 1 ? 'Comment' : 'Comments'}`;
            });
        }
    })
    .catch(error => {
        if (button) button.disabled = false;
        console.error('Error deleting comment:', error);
    });
    });
}

/**
 * Handle Follow submission via AJAX
 */
function handleFollowSubmit(form) {
    const button = form.querySelector('.follow-btn') || form.querySelector('button');
    if (!button) return;
    
    button.disabled = true;
    
    const url = form.getAttribute('action');
    const csrfToken = form.querySelector('input[name="_token"]').value;
    const userId = form.getAttribute('data-user-id');
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({})
    })
    .then(response => {
        if (!response.ok) throw new Error('Follow toggle failed');
        return response.json();
    })
    .then(data => {
        button.disabled = false;
        
        // Sync all follow buttons for this user on the page
        const allFormsForUser = document.querySelectorAll(`.follow-form[data-user-id="${userId}"]`);
        allFormsForUser.forEach(f => {
            const btn = f.querySelector('.follow-btn') || f.querySelector('button');
            if (btn) {
                if (btn.classList.contains('px-3') && btn.classList.contains('py-1.5')) {
                    // Search results page layout style
                    if (data.following) {
                        btn.textContent = 'Following';
                        btn.className = 'px-3 py-1.5 rounded-full text-xs font-semibold follow-btn border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors whitespace-nowrap';
                    } else {
                        btn.textContent = 'Follow';
                        btn.className = 'px-3 py-1.5 rounded-full text-xs font-semibold follow-btn bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white transition-colors whitespace-nowrap';
                    }
                } else if (btn.classList.contains('px-5') && btn.classList.contains('py-2')) {
                    // Profile show layout style
                    btn.textContent = data.following ? 'Unfollow' : 'Follow';
                } else {
                    // Feed sidebar suggestion list style
                    btn.textContent = data.following ? 'Following' : 'Follow';
                    if (data.following) {
                        btn.className = 'text-xs text-gray-400 dark:text-gray-500 font-medium follow-btn pointer-events-none transition-colors';
                    } else {
                        btn.className = 'text-xs text-green-600 dark:text-green-400 hover:underline font-medium follow-btn transition-colors';
                    }
                }
            }
        });
        
        const followersCounts = document.querySelectorAll(`[data-followers-count-for="${userId}"]`);
        followersCounts.forEach(el => {
            if (el.tagName === 'STRONG') {
                el.textContent = data.followers_count;
            } else {
                const strong = el.querySelector('strong');
                if (strong) {
                    strong.textContent = data.followers_count;
                } else {
                    el.textContent = `${data.followers_count} ${data.followers_count === 1 ? 'Follower' : 'Followers'}`;
                }
            }
        });
    })
    .catch(error => {
        button.disabled = false;
        console.error('Error toggling follow:', error);
    });
}
