# AI Assistant Guidelines

Welcome! Please follow these guidelines when assisting with development tasks in this project:

## 1. Understand Project State
Before starting any coding, research, or discussion task, you **MUST** read the following documents to understand the current system status:
* **Current Task Status**: Refer to [project_status.md](file:///c:/projects/socialmedia/.agents/project_status.md) to see completed and pending tasks.
* **Code Architecture**: Refer to [system_architecture.md](file:///c:/projects/socialmedia/.agents/system_architecture.md) to understand the database design, models, controllers, routing, and CSS/JS asset integration.

## 2. Communication Language
* Use **English** or **Malaysian Malay** (professional and friendly) to interact with the user, depending on the user's preference (default to the user's input language).

## 3. Coding Rules
* **Asset Compilation (Vite)**: If you add or modify external CSS/JS files, ensure they are registered in `vite.config.js` and run `npm run build` after changes are complete.
* **Dark Mode Initialization**: To avoid unread flashing (FOUC), ensure all pages use an inline initialization script in the `<head>` of the main layouts.
* **Comment Integrity**: Preserve all existing comments that are unrelated to your changes to prevent documentation regression.
* **Documentation Update (README.md)**: When a feature has been updated or improved, ensure you also update the project's [README.md](file:///c:/projects/socialmedia/README.md) file to reflect the changes.
