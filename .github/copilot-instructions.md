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
