/**
 * App Layout - Alert Auto-Close
 * 
 * Features:
 * - Auto-close flash messages (success/error alerts) after 5 seconds
 * - Smooth fade-out animation
 */

document.addEventListener('DOMContentLoaded', function() {
    /**
     * Auto-close alert messages after 5 seconds
     * Targets elements with class 'alert-message'
     */
    const alerts = document.querySelectorAll('.alert-message');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
