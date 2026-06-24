/**
 * Chat Show Page - Message Auto-Scroll
 * 
 * Features:
 * - Auto-scroll to latest message on page load
 * - Keeps conversation at bottom
 */

document.addEventListener('DOMContentLoaded', function() {
    /**
     * Scroll messages container to bottom to show latest messages
     */
    const container = document.getElementById('messages-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
});
