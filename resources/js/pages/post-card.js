/**
 * Post Card Component - Image Modal & Comments Toggle
 * 
 * Features:
 * - Full-screen image modal viewer with close functionality
 * - Comments section toggle (show/hide)
 * - Keyboard Escape key handling for modal
 */

/**
 * Open image modal for a specific post
 * @param {number} postId - Post ID to open modal for
 */
window.openImageModal = function(postId) {
    const modal = document.getElementById(`imageModal${postId}`);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
};

/**
 * Close image modal for a specific post
 * @param {number} postId - Post ID to close modal for
 * @param {Event} event - Optional click event (used for overlay close)
 */
window.closeImageModal = function(postId, event) {
    // If event exists and click wasn't on the overlay itself, don't close
    if (event && event.target.id !== `imageModal${postId}`) return;
    
    const modal = document.getElementById(`imageModal${postId}`);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
};

/**
 * Toggle comments section visibility for a post
 * @param {number} postId - Post ID to toggle comments for
 */
window.toggleComments = function(postId) {
    const commentsSection = document.getElementById(`comments-${postId}`);
    if (commentsSection) {
        commentsSection.classList.toggle('hidden');
    }
};

// Close modal on Escape key press
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            // Find all open modals and close them
            document.querySelectorAll('[id^="imageModal"]').forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            });
        }
    });
});
