# System Architecture

This document helps AI assistants quickly understand the file structure, database schema, views, and routing layout of the application.

## 1. Technology Stack
* **Backend**: Laravel 11.x (PHP 8.2+)
* **Database**: MySQL (managed via Eloquent ORM)
* **Frontend**: Blade Templates + Alpine.js + Tailwind CSS
* **Asset Bundler**: Vite 8.x

---

## 2. Main File Structure

### A. Data Models (`app/Models/`)
* **`User.php`**: Manages profiles, posts, followers, likes, comments, and messages. Includes helper method `isFollowing(User $user)`.
* **`Profile.php`**: Additional profile information (bio, website, location, cover photo, avatar). Linked via `hasOne` relation from `User`.
* **`Post.php`**: User posts. Has relations with `Like` and `Comment`.
* **`Follow.php`**: Follow relationships (`follower_id`, `following_id`).
* **`Message.php`**: Direct chat system (`sender_id`, `receiver_id`, `body`, `read_at`).
* **`Comment.php`** & **`Like.php`**: Models storing post interactions.
* **`Notification`**: Standard database notification model (Laravel built-in).
* **`Story.php`**: Represents a user's 24-hour story with an image and optional caption.

### B. Controllers (`app/Http/Controllers/`)
* **`FeedController.php`**: Loads posts from self and followed users for the main feed view.
* **`SearchController.php`**: Filters and searches users (excluding current logged-in user).
* **`FollowController.php`**: Toggles follow/unfollow and fires notifications.
* **`LikeController.php`** & **`CommentController.php`**: Handles likes/comments and dispatches notifications.
* **`NotificationController.php`**: Manages the notifications list and marking them as read.
* **`ChatController.php`**: Manages direct messages.
* **`ProfileController.php`**: Updates user profiles and serves avatar images from private storage.
* **`StoryController.php`**: Handles story uploads, validation, and deletion.

### C. Frontend Views (`resources/views/`)
* **`layouts/app.blade.php`**: Main layout file (includes theme script and unread notification badge).
* **`feed/`**: Post feed view templates.
* **`search/results.blade.php`**: Search result template.
* **`notifications/index.blade.php`**: Dashboard displaying list of notifications.
* **`chat/`**: Direct chat screens.
* **`profile/`**: Profile show and edit settings templates.

---

## 3. Routing
All web routes are defined in [routes/web.php](file:///c:/projects/socialmedia/routes/web.php).
* **Public Routes**: Image delivery endpoints (`/users/{user}/avatar` and `/users/{user}/cover`).
* **Authenticated Routes (auth middleware group)**:
  * `/feed` (main feed)
  * `/search` (user search)
  * `/posts` (post publishing & deletion)
  * `/stories` (story uploading & deletion)
  * `/posts/{post}/like` (toggling post likes)
  * `/posts/{post}/comments` (submitting post comments)
  * `/users/{user}/follow` (toggling follows)
  * `/chat` & `/chat/{user}` (conversations)
  * `/notifications` & `/notifications/{id}/read` (notifications)

---

## 4. Asset Compiling (Vite)
Assets are compiled via `vite.config.js` and loaded in Blade templates using `@vite(...)`.
Whenever a new entry point (CSS or JS file) is created, it must be added to the input list in `vite.config.js` and rebuilt using:
```bash
npm run build
```
Or run the hot-reload dev server:
```bash
npm run dev
```
