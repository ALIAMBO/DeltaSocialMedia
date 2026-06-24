/**
 * Feed Index Page - Post Creation with File Validation
 * 
 * Features:
 * - Image file validation (max 5MB) for new posts
 * - Real-time image preview before upload
 * - File size error alerts
 */

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

/**
 * Preview selected image before upload
 * @param {Event} event - File input change event
 */
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
