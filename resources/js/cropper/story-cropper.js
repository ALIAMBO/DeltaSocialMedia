/**
 * Story Image Cropper
 * Handles image selection, preview, and cropping for stories
 * Aspect ratio: 1:1 (Square)
 */

let storyCropper = null;

function initStoryCropper() {
    console.log('[Story Cropper] Initialize called');
    
    const image = document.getElementById('cropper-image');
    if (!image) {
        console.error('[Story Cropper] cropper-image element not found');
        return;
    }
    
    if (typeof Cropper === 'undefined') {
        console.error('[Story Cropper] Cropper.js not loaded');
        return;
    }
    
    if (storyCropper) { 
        console.log('[Story Cropper] Destroying existing instance');
        storyCropper.destroy(); 
        storyCropper = null; 
    }

    function startCropper() {
        console.log('[Story Cropper] Starting cropper instance');
        try {
            storyCropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.9,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                responsive: true,
                containerPointerEvents: 'all'
            });
            console.log('[Story Cropper] Cropper initialized successfully');
        } catch (e) {
            console.error('[Story Cropper] Failed to initialize:', e);
        }
    }

    if (image.complete && image.naturalWidth > 0) {
        console.log('[Story Cropper] Image already loaded, starting cropper');
        startCropper();
    } else {
        console.log('[Story Cropper] Waiting for image to load');
        image.onload = function() {
            image.onload = null;
            startCropper();
        };
    }
}

function resetStoryCropper() {
    console.log('[Story Cropper] Reset called');
    if (storyCropper) { 
        storyCropper.destroy(); 
        storyCropper = null; 
    }
}

function applyStoryCrop(callback) {
    console.log('[Story Cropper] Apply crop called');
    
    if (!storyCropper) {
        console.error('[Story Cropper] No cropper instance');
        return;
    }
    
    const canvas = storyCropper.getCroppedCanvas({ width: 1080, height: 1080 });
    if (!canvas) {
        console.error('[Story Cropper] Failed to get canvas');
        return;
    }
    
    canvas.toBlob(function(blob) {
        console.log('[Story Cropper] Crop applied, blob size:', blob.size);
        const fileInput = document.getElementById('story-image-input');
        if (!fileInput) {
            console.error('[Story Cropper] story-image-input not found');
            return;
        }
        
        const file = new File([blob], 'story.jpg', { type: 'image/jpeg' });
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        resetStoryCropper();
        
        if (callback) callback();
    }, 'image/jpeg', 0.9);
}

/**
 * Setup story form submission handler
 * Called once on page load
 */
export function setupStoryFormHandler() {
    console.log('[Story Cropper] Setting up form handler');
    
    const form = document.getElementById('story-form');
    if (!form) {
        console.error('[Story Cropper] Story form not found');
        return;
    }
    
    form.addEventListener('submit', function(e) {
        console.log('[Story Cropper] Form submit event');
        
        if (!storyCropper) {
            console.log('[Story Cropper] No cropper instance, submitting form as is');
            return;
        }
        
        console.log('[Story Cropper] Form submit intercepted, applying crop');
        e.preventDefault();
        
        applyStoryCrop(() => {
            console.log('[Story Cropper] Crop applied, submitting form');
            form.submit();
        });
    });
    
    console.log('[Story Cropper] Form handler setup complete');
}

// Export for module imports
export { initStoryCropper, resetStoryCropper, applyStoryCrop };

// Make globally available
window.initStoryCropper = initStoryCropper;
window.resetStoryCropper = resetStoryCropper;
window.applyStoryCrop = applyStoryCrop;

// Aliases for backward compatibility (Blade template uses these names)
window.initCropper = initStoryCropper;
window.resetCropper = resetStoryCropper;

console.log('[Story Cropper] Module loaded - functions exposed to window');


