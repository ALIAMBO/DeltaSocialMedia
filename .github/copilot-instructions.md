---
name: auto-readme-updates
description: "Automatically update README.md whenever new functions, commands, or features are added to the codebase"
---

# Auto-Update README.md

## Purpose
Ensure that the README.md file is kept up-to-date with the latest functions, commands, and features added to the project.

## Guidelines

Whenever you add or modify:
- **New controller methods or routes**
- **New commands** (Artisan commands, etc.)
- **New API endpoints**
- **New features or functionality**
- **New models or database tables**
- **New utility functions**

**Automatically update the relevant section in [README.md](README.md)** with:
- A brief description of what the new function/command does
- How to use it (if applicable)
- Which file(s) it's located in

## Sections to Keep Updated

Update these README sections as needed:
- **Features** - For new user-facing features
- **Usage** - For new commands or workflows
- **Directory Structure** - For new important files/folders
- **Database Schema** - For new tables or significant schema changes
- **Future Enhancements** - When you complete an item, move it from this list

## Implementation Notes

- Keep documentation concise and consistent with existing README style
- Include file paths as links when referencing code
- Test that the updated README is clear and helpful
- Maintain the current README structure and formatting

---

# JavaScript Organization Guidelines

## Purpose
Keep Blade templates clean and focused on HTML structure by separating JavaScript logic into dedicated files.

## Where to Place JavaScript

### JavaScript File Structure
```
resources/
├── js/
│   ├── pages/          ← Page-specific JavaScript
│   │   ├── profile-edit.js
│   │   ├── feed-index.js
│   │   └── ...
│   ├── components/     ← Reusable component logic
│   └── utils/          ← Utility functions
```

### Naming Convention
- **File naming**: `{blade-file-name}.js`
- Example: `profile/edit.blade.php` → `resources/js/pages/profile-edit.js`

## Guidelines When Creating/Modifying Blade Files

1. **Keep Blade Clean** - Move all `<script>` blocks to separate `.js` files
2. **One JS file per Blade** - Create a dedicated `.js` file for each complex Blade template
3. **Use Vite Import** - Include JavaScript using `@vite()` in `@push('scripts')`
4. **Blade Template Only** - HTML, forms, and conditional Blade logic only
5. **Pass Data to JS** - Use hidden elements or data attributes to pass Blade variables to JavaScript

### Example: Blade Template
```blade
<!-- resources/views/profile/edit.blade.php -->

<!-- Pass backend errors as JSON for JS to read -->
<div id="validation-errors" class="hidden">{{ json_encode($errors->messages()) }}</div>

<!-- Clean HTML only - no inline scripts -->
<div x-data="fileValidation()" x-init="init()">
    <!-- Form HTML here -->
</div>

@push('scripts')
    @vite('resources/js/pages/profile-edit.js')
@endpush
```

### Example: JavaScript File
```javascript
// resources/js/pages/profile-edit.js

window.fileValidation = function() {
    return {
        // Component logic here
        init() {
            // Initialize
        },
        // Methods...
    }
}
```

### Passing Data from Blade to JavaScript
**Option 1: Hidden Element (Recommended for complex data)**
```blade
<div id="validation-errors" class="hidden">{{ json_encode($errors->messages()) }}</div>
```

```javascript
checkForValidationErrors() {
    const container = document.getElementById('validation-errors');
    const errors = JSON.parse(container.textContent);
    // Use errors...
}
```

**Option 2: Data Attribute**
```blade
<div id="app" data-user-id="{{ auth()->id() }}" data-config="{{ json_encode($config) }}">
```

```javascript
const userId = document.getElementById('app').dataset.userId;
const config = JSON.parse(document.getElementById('app').dataset.config);
```

## Benefits
- ✅ Blade files remain focused on HTML structure
- ✅ JavaScript is testable and reusable
- ✅ Easier to maintain and debug
- ✅ Better code organization
- ✅ Vite automatically minifies and optimizes JavaScript
- ✅ Cleaner commit history (separate concerns)
