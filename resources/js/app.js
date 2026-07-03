import Alpine from 'alpinejs';
import './ajax-actions';

window.Alpine = Alpine;

Alpine.start();

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
