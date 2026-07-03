# SocialMedia Project Status

This document records the current status of the project (completed and future/pending tasks) to help any AI assistant quickly understand the system state.

## 1. Completed Tasks

### 🚀 Core System Features
* **User Authentication**: Login & Registration flow implemented using Laravel Breeze.
* **User Profiles**: Custom profiles containing bio, location, website, avatar, and cover photo uploads.
* **Main Feed Column**: Feed displaying posts from followed users.
* **Post Creation**: Users can create posts with text and/or images.
* **Likes & Comments**: Ability to like and comment on posts, delete own posts/comments (fully dynamic via AJAX).
* **Dynamic AJAX Actions**: Non-refresh, dynamic follows, likes, and comment submissions (creation & deletion) with micro-animations across feed, profiles, and search results.
* **Real-time Updates (WebSockets & Reverb)**: Instant message updates in the floating chat widget and instant notifications toasting without polling delays.
* **Search**: Search for users by name or email via the navbar.
* **Dark Mode**: Toggle button in navbar, localStorage preference persistence, and prefers-color-scheme detection.

### 🛠️ Important Bug Fixes
* **Vite Manifest Bug**: Resolved `ViteException` by registering `resources/js/dark-mode.js` in `vite.config.js`.
* **Self-Search Result Bug**: Fixed SQL operator precedence in `SearchController.php` so the logged-in user cannot search for and find themselves.
* **Follow Button State Bug**: Changed check from `auth()->user()->following->contains($user->id)` to `auth()->user()->isFollowing($user)` in `search/results.blade.php` to display correct follow button text.
* **Dark Mode Refresh Flash**: Solved FOUC (Flash of Unstyled Content) by moving the theme initialization inline script to `<head>`.
* **Profile Dropdown Flash**: Fixed Alpine.js menu flashing during page reloads by adding `x-cloak` and a global CSS hiding rule.

### 🔔 Database Notifications (Recently Added)
* Created `notifications` table migration.
* Created notification classes: `NewFollowNotification`, `NewLikeNotification`, and `NewCommentNotification`.
* Integrated automatic dispatch triggers in `FollowController`, `LikeController`, and `CommentController`.
* Added navigation bar menu link with dynamic red badge indicating the number of unread notifications.
* Created `/notifications` dashboard page to view notifications and mark them as read.

### 📖 Stories (Recently Added)
* Created `stories` table migration.
* Implemented `Story` model and defined relationships in `User` model.
* Created `StoryController` to handle story uploads and deletion.
* Modified `FeedController` to retrieve active (last 24 hours) stories.
* Built horizontal scrollable stories bar, upload modal, and Alpine.js slideshow stories viewer (with progress bars, auto-advance, and navigation control).

### 💬 Floating Chat Widget (Recently Added)
* Added asynchronous JSON API endpoints in `ChatController.php` for fetching conversations, contacts, messages, and sending messages.
* Registered AJAX endpoints in `routes/web.php`.
* Implemented the HTML markup and Alpine.js widget engine directly in `layouts/app.blade.php`.
* Built background auto-polling sync (every 5 seconds) to pull new messages and update unread counters dynamically.

### #️⃣ Hashtags & Mentions (Recently Added)
* Added `getFormattedBodyAttribute()` accessor in `Post.php` model to parse `#hashtag` and `@username` inside post bodies with caching to prevent N+1 queries.
* Updated `post-card.blade.php` component to display the parsed formatted post body.
* Updated `PostController.php` to extract mentions upon post creation and dispatch database `UserMentionedNotification` records as well as real-time broadcast events.
* Updated `FeedController.php` to support filtering posts by `tag` parameters.
* Updated `SearchController.php` to redirect hashtag search queries to the feed tag filter.
* Updated `NotificationController.php` and notifications lists to fetch mentioner profile details and render custom formatting.

### 📱 Phone View Formatting & Mobile Responsive Design (Recently Added)
* Implemented sticky bottom navigation bar for mobile viewports (`Feed`, `Search`, `Chats`, `Alerts`, `Profile`) with notification and inbox count badges.
* Hid the top navbar text links on mobile layouts.
* Added body bottom padding dynamically (`pb-16 md:pb-0`) to prevent layout overlaps.
* Repositioned the floating chat widget trigger on mobile screens (`bottom-20 md:bottom-6`) so it floats above the bottom navigation bar.
* Modified the grid column layout of Location & Website settings inputs (`grid-cols-1 sm:grid-cols-2`) to avoid screen squeeze.

---

## 2. Pending Tasks & Future Enhancements

Listed in order of priority:

1. **Block / Report Users**
   * *Description*: Allow users to block others from sending messages or viewing their profile/posts.
2. **Post Privacy Settings**
   * *Description*: Enable users to set posts as "Public" or "Followers Only".
3. **Post Sharing / Reposting**
   * *Description*: Allow users to repost another user's post to their own profile timeline.
4. **Video Uploads**
   * *Description*: Support video clip uploads inside posts.
