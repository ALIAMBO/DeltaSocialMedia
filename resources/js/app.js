import Alpine from 'alpinejs';
import './ajax-actions';

window.Alpine = Alpine;

// Echo — wrapped so a bad config on production never crashes the page
import('./echo').catch(function(e) {
    console.warn('Echo init failed (non-fatal):', e && e.message ? e.message : e);
});

// Mentions suggestions autocomplete engine
document.addEventListener('DOMContentLoaded', () => {
    const textareas = document.querySelectorAll('textarea[name="body"]');
    
    textareas.forEach(textarea => {
        const dropdown = document.createElement('div');
        dropdown.className = "absolute z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden transition-colors w-full left-0";
        dropdown.style.top = (textarea.offsetTop + textarea.offsetHeight) + 'px';
        
        const parent = textarea.parentNode;
        parent.style.position = 'relative';
        parent.appendChild(dropdown);
        
        let activeIndex = -1;
        let suggestions = [];
        
        textarea.addEventListener('input', () => {
            const text = textarea.value;
            const selectionStart = textarea.selectionStart;
            const textBeforeCursor = text.substring(0, selectionStart);
            const match = textBeforeCursor.match(/@([\w\-]*)$/);
            if (match) {
                fetchSuggestions(match[1]);
            } else {
                hideDropdown();
            }
        });
        
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
                item.addEventListener('click', () => { selectUser(user); });
                dropdown.appendChild(item);
            });
            dropdown.style.top = (textarea.offsetTop + textarea.offsetHeight) + 'px';
            dropdown.classList.remove('hidden');
        }
        
        function updateActiveItem(items) {
            items.forEach(item => { item.classList.remove('bg-green-50', 'dark:bg-green-950/20'); });
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
            const lastAtIdx = textBeforeCursor.lastIndexOf('@');
            if (lastAtIdx === -1) return;
            const beforeMention = text.substring(0, lastAtIdx);
            const mentionText = `@${user.slug} `;
            textarea.value = beforeMention + mentionText + textAfterCursor;
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

// Auto-open comments and scroll to post when hash is present
document.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#post-')) {
        const postId = hash.split('-')[1];
        const commentsDiv = document.getElementById(`comments-${postId}`);
        if (commentsDiv) commentsDiv.classList.remove('hidden');
        setTimeout(() => {
            const postElement = document.getElementById(`post-${postId}`);
            if (postElement) postElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 150);
    }
});

/**
 * Engine: Theme-Adaptive Specular Haptic Engine
 * Tracks user interaction metrics and transforms glare arrays without triggering main-thread layout thrashing.
 */
function initializeLiquidGlassEngine() {
    const glassElements = document.querySelectorAll('.liquid-glass-card');
    glassElements.forEach(element => {
        const glareLayer = element.querySelector('.liquid-glass-glare');
        if (!glareLayer) return;
        
        // Mouse Move Event: Track spatial positioning parameters
        element.addEventListener('mousemove', (event) => {
            const boundaries = element.getBoundingClientRect();
            const absoluteX = event.clientX - boundaries.left;
            const absoluteY = event.clientY - boundaries.top;
            glareLayer.style.setProperty('--mouse-x', `${absoluteX}px`);
            glareLayer.style.setProperty('--mouse-y', `${absoluteY}px`);
            glareLayer.style.opacity = '1';
        }, { passive: true });
        
        // Mouse Leave Event: Reset coordinate glare alpha values smoothly
        element.addEventListener('mouseleave', () => {
            glareLayer.style.opacity = '0';
        });
    });
}

// Frame Lifecycle Initializers
document.addEventListener('DOMContentLoaded', initializeLiquidGlassEngine);
window.initializeLiquidGlassEngine = initializeLiquidGlassEngine;

// Compatibility Layer for Framework Ecosystems (Livewire / Alpine AJAX Navigation)
if (window.Livewire) {
    document.addEventListener('livewire:navigated', initializeLiquidGlassEngine);
}

Alpine.start();

