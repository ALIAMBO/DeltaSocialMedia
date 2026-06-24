/**
 * Profile Edit Page - Form Validation & File Upload Management
 * 
 * Features:
 * - File validation modal for avatar and cover photo uploads
 * - Backend error detection and display as modal popup
 * - Event listeners for file input management
 * - Alpine.js integration for reactive UI
 */

window.fileValidation = function() {
    return {
        showModal: false,
        modalMessage: '',

        // Configuration
        MAX_FILE_SIZE: 5 * 1024 * 1024, // 5MB per file
        MAX_FILE_SIZE_MB: 5,
        MAX_COMBINED_SIZE: 7.5 * 1024 * 1024, // 7.5MB combined
        MAX_COMBINED_SIZE_MB: 7.5,

        /**
         * Open modal with a custom message
         * @param {string} message - The error message to display
         */
        openModal(message) {
            this.modalMessage = message;
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },

        /**
         * Close the modal and restore page scrolling
         */
        closeModal() {
            this.showModal = false;
            document.body.style.overflow = 'auto';
        },

        /**
         * Handle form submission - allow backend to validate
         * @param {Event} e - Form submission event
         */
        handleSubmit(e) {
            // Just allow form submission - backend will validate and return errors
        },

        /**
         * Initialize all event listeners for file uploads
         * Only runs once to prevent duplicate listeners
         */
        init() {
            const self = this;
            
            // Prevent double initialization
            if (this._initialized) return;
            this._initialized = true;

            // Avatar wrapper click - trigger file input
            const avatarWrapper = document.getElementById('avatar-wrapper');
            if (avatarWrapper) {
                avatarWrapper.addEventListener('click', () => {
                    document.getElementById('avatar-input').click();
                });
            }

            // Cover wrapper click - trigger file input
            const coverWrapper = document.getElementById('cover-wrapper');
            if (coverWrapper) {
                coverWrapper.addEventListener('click', () => {
                    document.getElementById('cover-input').click();
                });
            }

            // Avatar file input change - preview the selected image
            const avatarInput = document.getElementById('avatar-input');
            if (avatarInput) {
                avatarInput.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (event) => {
                        document.getElementById('avatar-preview').src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            }

            // Cover file input change - preview the selected image
            const coverInput = document.getElementById('cover-input');
            if (coverInput) {
                coverInput.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const img = document.getElementById('cover-img');
                        img.src = event.target.result;
                        img.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                });
            }

            // Check for backend validation errors from page load
            this.checkForValidationErrors();
        },

        /**
         * Check for backend validation errors and display as modal
         * Reads errors from data attribute set by Blade template
         */
        checkForValidationErrors() {
            // Get the div containing validation errors (set by Blade)
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
