/**
 * Profile Edit Page - Form Validation & File Upload Management
 * 
 * Features:
 * - File validation modal for avatar and cover photo uploads
 * - Backend error detection and display as modal popup
 * - Event listeners for file input management
 * - Alpine.js integration for reactive UI
 * - Image cropping for avatar and cover photos
 */

import { setupProfileImageUpload } from '../cropper/profile-cropper.js';

console.log('[Profile Edit] Module loaded');

// Wait for Cropper.js library to be fully available
function waitForCropper(callback, maxAttempts = 50) {
    let attempts = 0;
    const checkInterval = setInterval(() => {
        attempts++;
        if (typeof window.Cropper !== 'undefined') {
            console.log('[Profile Edit] Cropper library is available');
            clearInterval(checkInterval);
            callback();
        } else if (attempts >= maxAttempts) {
            console.error('[Profile Edit] Cropper library failed to load after', maxAttempts, 'attempts');
            clearInterval(checkInterval);
        }
    }, 100);
}

window.fileValidation = function() {
    return {
        showModal: false,
        modalMessage: '',

        // Configuration
        MAX_FILE_SIZE: 5 * 1024 * 1024, // 5MB per file
        MAX_FILE_SIZE_MB: 5,

        openModal(message) {
            this.modalMessage = message;
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },

        closeModal() {
            this.showModal = false;
            document.body.style.overflow = 'auto';
        },

        handleSubmit(e) {
            // Just allow form submission - backend will validate and return errors
        },

        init() {
            console.log('[Profile Edit] Alpine.js init called');
            
            // Prevent double initialization
            if (this._initialized) {
                console.log('[Profile Edit] Already initialized, skipping');
                return;
            }
            this._initialized = true;

            // Avatar wrapper click - trigger file input
            const avatarWrapper = document.getElementById('avatar-wrapper');
            if (avatarWrapper) {
                avatarWrapper.addEventListener('click', (e) => {
                    console.log('[Profile Edit] Avatar wrapper clicked');
                    e.preventDefault();
                    e.stopPropagation();
                    const input = document.getElementById('avatar-input');
                    if (input) input.click();
                });
                console.log('[Profile Edit] Avatar wrapper click listener added');
            } else {
                console.error('[Profile Edit] Avatar wrapper not found');
            }

            // Cover wrapper click - trigger file input
            const coverWrapper = document.getElementById('cover-wrapper');
            if (coverWrapper) {
                coverWrapper.addEventListener('click', (e) => {
                    console.log('[Profile Edit] Cover wrapper clicked');
                    e.preventDefault();
                    e.stopPropagation();
                    const input = document.getElementById('cover-input');
                    if (input) input.click();
                });
                console.log('[Profile Edit] Cover wrapper click listener added');
            } else {
                console.error('[Profile Edit] Cover wrapper not found');
            }

            // Setup image cropping functionality
            waitForCropper(() => {
                console.log('[Profile Edit] Setting up profile image upload');
                setupProfileImageUpload();
            });

            // Check for backend validation errors
            this.checkForValidationErrors();
        },

        checkForValidationErrors() {
            const errorContainer = document.getElementById('validation-errors');
            if (!errorContainer) return;

            try {
                const errors = JSON.parse(errorContainer.textContent);
                
                if (errors.avatar && errors.avatar[0]) {
                    this.openModal(`⚠️ Avatar Error\n\n${errors.avatar[0]}`);
                } else if (errors.cover_photo && errors.cover_photo[0]) {
                    this.openModal(`⚠️ Cover Photo Error\n\n${errors.cover_photo[0]}`);
                }
            } catch (e) {
                // Silently fail if no errors or JSON parse error
            }
        }
    }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        console.log('[Profile Edit] DOMContentLoaded fired');
        // Ensure setupProfileImageUpload is called on DOM ready
        waitForCropper(() => {
            console.log('[Profile Edit] Calling setupProfileImageUpload on DOM ready');
            setupProfileImageUpload();
        });
    });
} else {
    console.log('[Profile Edit] DOM already loaded, initializing now');
    // Call immediately if DOM is already ready
    waitForCropper(() => {
        console.log('[Profile Edit] Calling setupProfileImageUpload (DOM pre-loaded)');
        setupProfileImageUpload();
    });
}
