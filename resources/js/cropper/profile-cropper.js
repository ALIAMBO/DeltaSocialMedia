/**
 * Profile Image Cropper
 * Handles image selection, preview, and cropping for profile avatar and cover photos
 */

let profileCropInstance = null;
let currentCropTarget = null; // 'avatar' or 'cover'

const ASPECT_RATIOS = {
    avatar: 1,      // 1:1 Square
    cover: 3        // 3:1 Landscape
};

const CANVAS_SIZES = {
    avatar: { width: 500, height: 500 },
    cover: { width: 1500, height: 500 }
};

function getModal() {
    return document.getElementById('cropper-modal');
}

function getCropperImg() {
    return document.getElementById('cropper-img');
}

function getTitleEl() {
    return document.getElementById('cropper-title');
}

window.openCropModal = function(target, src, title) {
    console.log('[Cropper] Opening modal for:', target);
    
    // Validate Cropper is loaded
    if (typeof Cropper === 'undefined') {
        console.error('Cropper.js library not loaded!');
        alert('Cropper library failed to load. Please refresh the page.');
        return;
    }
    
    currentCropTarget = target;
    const modal = getModal();
    const img = getCropperImg();
    const titleEl = getTitleEl();
    
    if (!modal || !img) {
        console.error('Modal or image element not found');
        return;
    }
    
    if (titleEl) titleEl.textContent = title;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    if (profileCropInstance) { 
        profileCropInstance.destroy(); 
        profileCropInstance = null; 
    }

    function startCrop() {
        if (profileCropInstance) { 
            profileCropInstance.destroy(); 
            profileCropInstance = null; 
        }
        
        const aspectRatio = ASPECT_RATIOS[target] || 1;
        console.log('[Cropper] Starting crop with aspect ratio:', aspectRatio);
        
        try {
            profileCropInstance = new Cropper(img, {
                aspectRatio: aspectRatio,
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
            console.log('[Cropper] Cropper initialized successfully');
        } catch (e) {
            console.error('[Cropper] Failed to initialize Cropper:', e);
        }
    }

    img.onload = function() { 
        img.onload = null; 
        startCrop(); 
    };
    img.src = src;
    
    // Data URLs are decoded synchronously in most browsers
    if (img.complete && img.naturalWidth > 0) { 
        img.onload = null; 
        startCrop(); 
    }
};

window.cancelCrop = function() {
    console.log('[Cropper] Cancelling crop for:', currentCropTarget);
    
    if (profileCropInstance) { 
        profileCropInstance.destroy(); 
        profileCropInstance = null; 
    }
    
    // Reset the file input for whichever target was being cropped
    if (currentCropTarget === 'avatar') {
        const input = document.getElementById('avatar-input');
        if (input) input.value = '';
    } else if (currentCropTarget === 'cover') {
        const input = document.getElementById('cover-input');
        if (input) input.value = '';
    }
    
    currentCropTarget = null;
    const modal = getModal();
    if (modal) modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
};

window.applyCrop = function() {
    console.log('[Cropper] Applying crop for:', currentCropTarget);
    
    if (!profileCropInstance || !currentCropTarget) {
        console.error('[Cropper] No active crop instance or target');
        return;
    }
    
    const sizes = CANVAS_SIZES[currentCropTarget];
    const canvas = profileCropInstance.getCroppedCanvas({ 
        width: sizes.width, 
        height: sizes.height,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high'
    });
    
    if (!canvas) {
        console.error('[Cropper] Failed to get cropped canvas');
        return;
    }
    
    canvas.toBlob(function(blob) {
        console.log('[Cropper] Cropped blob created, size:', blob.size, 'bytes');
        
        const filename = currentCropTarget === 'cover' ? 'cover.jpg' : 'avatar.jpg';
        const file = new File([blob], filename, { type: 'image/jpeg' });
        const dt = new DataTransfer();
        dt.items.add(file);

        if (currentCropTarget === 'avatar') {
            const input = document.getElementById('avatar-input');
            const preview = document.getElementById('avatar-preview');
            if (input && preview) {
                input.files = dt.files;
                if (preview.src && preview.src.startsWith('blob:')) {
                    URL.revokeObjectURL(preview.src);
                }
                preview.src = URL.createObjectURL(blob);
                console.log('[Cropper] Avatar updated');
            }
        } else if (currentCropTarget === 'cover') {
            const input = document.getElementById('cover-input');
            const coverImg = document.getElementById('cover-img');
            if (input && coverImg) {
                input.files = dt.files;
                if (coverImg.src && coverImg.src.startsWith('blob:')) {
                    URL.revokeObjectURL(coverImg.src);
                }
                coverImg.src = URL.createObjectURL(blob);
                coverImg.classList.remove('hidden');
                console.log('[Cropper] Cover updated');
            }
        }

        if (profileCropInstance) { 
            profileCropInstance.destroy(); 
            profileCropInstance = null; 
        }
        
        currentCropTarget = null;
        const modal = getModal();
        if (modal) modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        console.log('[Cropper] Crop applied and modal closed');
    }, 'image/jpeg', 0.9);
};

// Expose setup function for module import
export function setupProfileImageUpload() {
    console.log('[Cropper] Setting up profile image upload handlers');
    
    // Avatar file change → open crop modal
    const avatarInput = document.getElementById('avatar-input');
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            console.log('[Cropper] Avatar file selected:', file.name, file.size, 'bytes');
            
            if (file.size > 5 * 1024 * 1024) {
                alert('Image must be less than 5MB.');
                e.target.value = '';
                return;
            }
            
            window.openCropModal('avatar', URL.createObjectURL(file), 'Crop Profile Picture');
        });
        console.log('[Cropper] Avatar input listener attached');
    }

    // Cover file change → open crop modal
    const coverInput = document.getElementById('cover-input');
    if (coverInput) {
        coverInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            console.log('[Cropper] Cover file selected:', file.name, file.size, 'bytes');
            
            if (file.size > 5 * 1024 * 1024) {
                alert('Image must be less than 5MB.');
                e.target.value = '';
                return;
            }
            
            window.openCropModal('cover', URL.createObjectURL(file), 'Crop Cover Photo');
        });
        console.log('[Cropper] Cover input listener attached');
    }
    
    console.log('[Cropper] Profile image upload setup complete');
}

// Expose all functions to window for global access
window.setupProfileImageUpload = setupProfileImageUpload;

console.log('[Cropper] Module loaded - functions exposed to window');
