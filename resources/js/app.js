import Alpine from 'alpinejs';
import './ajax-actions';
import Cropper from 'cropperjs/dist/cropper.esm.js';
import 'cropperjs/dist/cropper.min.css';

window.Alpine = Alpine;
window.Cropper = Cropper;

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

// Mentions suggestions autocomplete engine
document.addEventListener('DOMContentLoaded', () => {
    const textareas = document.querySelectorAll('textarea[name="body"]');
    
    textareas.forEach(textarea => {
        // Create suggestion dropdown
        const dropdown = document.createElement('div');
        dropdown.className = "absolute z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden transition-colors w-full left-0";
        // Position it below the textarea
        dropdown.style.top = (textarea.offsetTop + textarea.offsetHeight) + 'px';
        
        // Wrap parent relative positioning to align dropdown
        const parent = textarea.parentNode;
        parent.style.position = 'relative';
        parent.appendChild(dropdown);
        
        let activeIndex = -1;
        let suggestions = [];
        
        // Listen to input
        textarea.addEventListener('input', () => {
            const text = textarea.value;
            const selectionStart = textarea.selectionStart;
            const textBeforeCursor = text.substring(0, selectionStart);
            const match = textBeforeCursor.match(/@([\w\-]*)$/);
            
            if (match) {
                const query = match[1];
                fetchSuggestions(query);
            } else {
                hideDropdown();
            }
        });
        
        // Handle keyboard navigation
        textarea.addEventListener('keydown', (e) => {
            if (dropdown.classList.contains('hidden')) return;
            
            const items = dropdown.querySelectorAll('.mention-suggestion-item');
            if (items.length === 0) return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                updateActiveItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                updateActiveItem(items);
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && activeIndex < items.length) {
                    e.preventDefault();
                    selectUser(suggestions[activeIndex]);
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                hideDropdown();
            }
        });
        
        // Hide dropdown when clicking away
        document.addEventListener('click', (e) => {
            if (!textarea.contains(e.target) && !dropdown.contains(e.target)) {
                hideDropdown();
            }
        });
        
        function fetchSuggestions(query) {
            fetch(`/api/following/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    suggestions = data;
                    if (suggestions.length > 0) {
                        renderDropdown(suggestions);
                    } else {
                        hideDropdown();
                    }
                })
                .catch(() => hideDropdown());
        }
        
        function renderDropdown(users) {
            dropdown.innerHTML = '';
            activeIndex = -1;
            
            users.forEach((user, index) => {
                const item = document.createElement('div');
                item.className = "mention-suggestion-item flex items-center gap-3 px-4 py-2 hover:bg-green-50 dark:hover:bg-green-950/20 cursor-pointer text-sm text-gray-705 dark:text-gray-200 transition-colors";
                item.dataset.index = index;
                
                item.innerHTML = `
                    <img src="${user.avatar}" class="w-6 h-6 rounded-full object-cover border border-gray-200 dark:border-gray-700">
                    <span class="font-semibold">${user.name}</span>
                `;
                
                item.addEventListener('click', () => {
                    selectUser(user);
                });
                
                dropdown.appendChild(item);
            });
            
            dropdown.style.top = (textarea.offsetTop + textarea.offsetHeight) + 'px';
            dropdown.classList.remove('hidden');
        }
        
        function updateActiveItem(items) {
            items.forEach(item => {
                item.classList.remove('bg-green-50', 'dark:bg-green-950/20');
            });
            if (activeIndex >= 0 && activeIndex < items.length) {
                const activeItem = items[activeIndex];
                activeItem.classList.add('bg-green-50', 'dark:bg-green-950/20');
                activeItem.scrollIntoView({ block: 'nearest' });
            }
        }
        
        function selectUser(user) {
            const text = textarea.value;
            const selectionStart = textarea.selectionStart;
            const textBeforeCursor = text.substring(0, selectionStart);
            const textAfterCursor = text.substring(selectionStart);
            
            // Find start index of last '@'
            const lastAtIdx = textBeforeCursor.lastIndexOf('@');
            if (lastAtIdx === -1) return;
            
            const beforeMention = text.substring(0, lastAtIdx);
            
            // Autocomplete with slugified username
            const mentionText = `@${user.slug} `;
            textarea.value = beforeMention + mentionText + textAfterCursor;
            
            // Focus and put cursor at end of mention
            textarea.focus();
            const newCursorPos = lastAtIdx + mentionText.length;
            textarea.setSelectionRange(newCursorPos, newCursorPos);
            
            hideDropdown();
        }
        
        function hideDropdown() {
            dropdown.innerHTML = '';
            dropdown.classList.add('hidden');
            activeIndex = -1;
            suggestions = [];
        }
    });
});

// Auto-open comments and scroll to post when hash is present in URL
document.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#post-')) {
        const postId = hash.split('-')[1];
        const commentsDiv = document.getElementById(`comments-${postId}`);
        if (commentsDiv) {
            commentsDiv.classList.remove('hidden');
        }
        
        // Wait a tiny bit for rendering, then scroll to it
        setTimeout(() => {
            const postElement = document.getElementById(`post-${postId}`);
            if (postElement) {
                postElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 150);
    }
});

// Unified PhotoManager for all cropping and upload management
window.PhotoManager = {
    instances: {},

    register({ id, inputEl, previewImgEl, formEl, aspectRatio = 1, modalEl = null, modalImgEl = null, modalTitleEl = null, modalTitle = 'Crop Image', onCropApplied = null }) {
        const self = this;

        // Save reference
        this.instances[id] = {
            cropper: null,
            inputEl,
            previewImgEl,
            modalEl,
            modalImgEl
        };

        // File selection handler
        inputEl.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            // Validate size (5MB)
            const MAX_FILE_SIZE = 5 * 1024 * 1024;
            if (file.size > MAX_FILE_SIZE) {
                alert(`⚠️ File size too large!\n\n"${file.name}" is ${(file.size / (1024 * 1024)).toFixed(2)}MB.\n\nMaximum allowed: 5MB`);
                e.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                if (modalEl && modalImgEl) {
                    // Modal-based crop
                    if (modalTitleEl) {
                        modalTitleEl.textContent = modalTitle;
                    }
                    modalEl.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    modalImgEl.src = event.target.result;

                    if (self.instances[id].cropper) {
                        self.instances[id].cropper.destroy();
                        self.instances[id].cropper = null;
                    }

                    setTimeout(() => {
                        self.instances[id].cropper = new Cropper(modalImgEl, {
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
                            toggleDragModeOnDblclick: false
                        });
                    }, 50);
                } else {
                    // Inline crop
                    previewImgEl.parentNode.parentNode.classList.remove('hidden'); // Show image-preview div
                    previewImgEl.src = event.target.result;

                    if (self.instances[id].cropper) {
                        self.instances[id].cropper.destroy();
                        self.instances[id].cropper = null;
                    }

                    setTimeout(() => {
                        self.instances[id].cropper = new Cropper(previewImgEl, {
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
                            toggleDragModeOnDblclick: false
                        });
                    }, 50);
                }
            };
            reader.readAsDataURL(file);
        });

        // If formEl is provided, intercept form submission to replace files with cropped blobs
        if (formEl && !formEl.dataset.cropperIntercepted) {
            formEl.dataset.cropperIntercepted = "true";
            formEl.addEventListener('submit', (event) => {
                // Find all active cropped instances of this form
                const activeCropInstances = Object.values(self.instances).filter(inst => {
                    return inst.cropper && (inst.inputEl.form === formEl);
                });

                if (activeCropInstances.length > 0) {
                    event.preventDefault();
                    let processedCount = 0;

                    const submitWithBlobs = () => {
                        formEl.submit();
                    };

                    activeCropInstances.forEach((inst) => {
                        const canvas = inst.cropper.getCroppedCanvas();
                        if (canvas) {
                            canvas.toBlob((blob) => {
                                const file = new File([blob], 'cropped.jpg', { type: 'image/jpeg' });
                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(file);
                                inst.inputEl.files = dataTransfer.files;

                                // Destroy cropper
                                inst.cropper.destroy();
                                inst.cropper = null;

                                processedCount++;
                                if (processedCount === activeCropInstances.length) {
                                    submitWithBlobs();
                                }
                            }, 'image/jpeg', 0.9);
                        } else {
                            processedCount++;
                            if (processedCount === activeCropInstances.length) {
                                submitWithBlobs();
                            }
                        }
                    });
                }
            });
        }
    },

    applyModalCrop(id, callback = null) {
        const inst = this.instances[id];
        if (!inst || !inst.cropper) return;

        const canvas = inst.cropper.getCroppedCanvas();
        if (canvas) {
            canvas.toBlob((blob) => {
                const file = new File([blob], 'cropped.jpg', { type: 'image/jpeg' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                inst.inputEl.files = dataTransfer.files;

                // Update page preview
                if (inst.previewImgEl) {
                    inst.previewImgEl.src = canvas.toDataURL('image/jpeg', 0.9);
                    inst.previewImgEl.classList.remove('hidden');
                }

                if (callback) {
                    callback(canvas.toDataURL('image/jpeg', 0.9));
                }

                this.closeModal(id);
            }, 'image/jpeg', 0.9);
        }
    },

    cancelCrop(id) {
        const inst = this.instances[id];
        if (!inst) return;

        if (inst.cropper) {
            inst.cropper.destroy();
            inst.cropper = null;
        }

        inst.inputEl.value = '';

        // If inline preview, reset preview image
        if (!inst.modalEl) {
            if (inst.previewImgEl) {
                inst.previewImgEl.removeAttribute('src');
                inst.previewImgEl.parentNode.parentNode.classList.add('hidden');
            }
        } else {
            this.closeModal(id);
        }
    },

    closeModal(id) {
        const inst = this.instances[id];
        if (inst && inst.modalEl) {
            inst.modalEl.classList.add('hidden');
            document.body.style.overflow = 'auto';
            if (inst.cropper) {
                inst.cropper.destroy();
                inst.cropper = null;
            }
        }
    }
};

Alpine.start();
