/**
 * Post Image Cropper
 * Handles image selection, preview, and cropping for posts
 * Aspect ratio: 1:1 (Square)
 */

let postCropper = null;

function initPostCropper() {
    const img = document.getElementById('preview-img');
    if (!img) {
        console.error('[Post Cropper] preview-img element not found');
        return;
    }
    
    console.log('[Post Cropper] Initializing cropper');
    
    if (typeof Cropper === 'undefined') {
        console.error('[Post Cropper] Cropper.js not loaded');
        return;
    }
    
    if (postCropper) { 
        postCropper.destroy(); 
        postCropper = null; 
    }
    
    try {
        postCropper = new Cropper(img, {
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
        console.log('[Post Cropper] Cropper initialized successfully');
    } catch (e) {
        console.error('[Post Cropper] Failed to initialize:', e);
    }
}

function resetPostCropper() {
    console.log('[Post Cropper] Resetting cropper');
    if (postCropper) { 
        postCropper.destroy(); 
        postCropper = null; 
    }
}

function cancelPostImage() {
    console.log('[Post Cropper] Cancel called');
    resetPostCropper();
    const input = document.querySelector('#post-create-form input[name="image"]');
    if (input) input.value = '';
    const preview = document.getElementById('image-preview');
    if (preview) preview.classList.add('hidden');
    const img = document.getElementById('preview-img');
    if (img) {
        if (img.src && img.src.startsWith('blob:')) {
            URL.revokeObjectURL(img.src);
        }
        img.removeAttribute('src');
    }
}

function applyPostCrop() {
    console.log('[Post Cropper] Applying crop');
    if (!postCropper) {
        console.error('[Post Cropper] No cropper instance');
        return;
    }
    
    const canvas = postCropper.getCroppedCanvas({ width: 1080, height: 1080 });
    if (!canvas) {
        console.error('[Post Cropper] Failed to get canvas');
        return;
    }
    
    canvas.toBlob(function(blob) {
        console.log('[Post Cropper] Crop applied, blob size:', blob.size);
        const input = document.querySelector('#post-create-form input[name="image"]');
        const file = new File([blob], 'post.jpg', { type: 'image/jpeg' });
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        resetPostCropper();
    }, 'image/jpeg', 0.9);
}

/**
 * Initialize post image upload handler
 */
export function setupPostImageUpload() {
    console.log('[Post Cropper] Setting up post image upload');
    
    const input = document.querySelector('#post-create-form input[name="image"]');
    if (!input) {
        console.error('[Post Cropper] Form input not found');
        return;
    }

    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        console.log('[Post Cropper] File selected:', file.name, file.size, 'bytes');
        
        if (file.size > 5 * 1024 * 1024) {
            alert('Image must be less than 5MB.');
            e.target.value = '';
            return;
        }
        
        const preview = document.getElementById('image-preview');
        const img = document.getElementById('preview-img');
        if (preview) preview.classList.remove('hidden');
        resetPostCropper();
        
        img.onload = function() {
            img.onload = null;
            initPostCropper();
        };
        img.src = URL.createObjectURL(file);
        
        // Handle case where image is cached (onload won't fire)
        if (img.complete && img.naturalWidth > 0) {
            initPostCropper();
        }
    });

    const form = document.getElementById('post-create-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!postCropper) {
                console.log('[Post Cropper] No cropper, submitting form as is');
                return;
            }
            
            console.log('[Post Cropper] Form submit intercepted, applying crop');
            e.preventDefault();
            
            const canvas = postCropper.getCroppedCanvas({ width: 1080, height: 1080 });
            if (canvas) {
                canvas.toBlob(function(blob) {
                    console.log('[Post Cropper] Final crop applied');
                    const input = document.querySelector('#post-create-form input[name="image"]');
                    const file = new File([blob], 'post.jpg', { type: 'image/jpeg' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                    resetPostCropper();
                    form.submit();
                }, 'image/jpeg', 0.9);
            } else {
                resetPostCropper();
                form.submit();
            }
        });
    }
    
    console.log('[Post Cropper] Setup complete');
}

// Export for module imports
export { cancelPostImage, initPostCropper, applyPostCrop };

// Make globally available
window.cancelPostImage = cancelPostImage;
window.initPostCropper = initPostCropper;
window.applyPostCrop = applyPostCrop;


