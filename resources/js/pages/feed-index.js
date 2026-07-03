/**
 * Feed Index Page - Post and Story Creation with Image Cropping
 * 
 * Features:
 * - Image file validation (max 5MB) for posts and stories
 * - Real-time image preview before upload
 * - Image cropping for posts (1:1 aspect ratio)
 * - Image cropping for stories (9:16 aspect ratio)
 * - File size error alerts
 */

import { setupPostImageUpload, cancelPostImage } from '../cropper/post-cropper.js';
import { setupStoryFormHandler, initStoryCropper, resetStoryCropper } from '../cropper/story-cropper.js';

const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const MAX_FILE_SIZE_MB = 5;

/**
 * Show file size warning alert
 * @param {string} fileName - Name of the file
 * @param {number} fileSize - Size in bytes
 */
function showFileSizeWarning(fileName, fileSize) {
    const fileSizeMB = (fileSize / (1024 * 1024)).toFixed(2);
    alert(`⚠️ File size too large!\n\n"${fileName}" is ${fileSizeMB}MB.\n\nMaximum allowed: ${MAX_FILE_SIZE_MB}MB`);
}

/**
 * Validate if file size is within limits
 * @param {File} file - File object to validate
 * @returns {boolean} True if valid, false otherwise
 */
function validateFileSize(file) {
    if (file.size > MAX_FILE_SIZE) {
        showFileSizeWarning(file.name, file.size);
        return false;
    }
    return true;
}

// Initialize all handlers on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    setupPostImageUpload();
    setupStoryFormHandler();
});

// Make post cropper functions available globally for HTML onclick handlers
window.cancelPostImage = cancelPostImage;

// Make story cropper functions available globally for Alpine.js directives
window.initStoryCropper = initStoryCropper;
window.resetStoryCropper = resetStoryCropper;
