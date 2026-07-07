# SocialMedia - Laravel Social Network

A full-featured social media platform built with Laravel 11, Tailwind CSS, and MySQL.

## Features

### ✅ Authentication
- Login & Registration with Laravel Breeze
- Password reset functionality

### 👤 User Profiles
- Custom profile pages with bio, location, website
- Avatar and cover photo uploads
- **Click avatar to view full-size profile picture** in scrollable modal
- View followers & following counts (updated dynamically)
- Follow/unfollow users dynamically via AJAX with instant button and count updates
- Profile link in avatar dropdown menu (navbar)

### 📝 Posts & Feed
- Create posts with text and/or images
- View feed from followed users
- **AJAX-driven Likes & Comments**: Like/unlike posts and submit comments dynamically with zero page refresh and smooth micro-animations
- Delete own posts, and delete comments instantly via background AJAX requests

### 💬 Real-time Messaging & WebSockets
- **WebSocket-powered Chat**: Direct chat messages update instantly in the floating chat widget in real-time using Laravel Reverb
- View all conversations, with messages sorted by most recent
- Message read status synced in real-time
- **Real-time Notification Toasts**: Like, comment, and follow notifications are toasted on the screen and update the count badge instantly without delay

### 🔍 Search
- Search for users by name or email
- Search available in navbar on all pages
- View user profiles with follow options from search results
- Minimum 2 character search requirement

### 🔔 Notifications
- Database-driven notification system
- Automatic notifications triggered for new follows, post likes, and comments
- Bell icon with a dynamic red dot notification count badge in the navbar
- Dedicated dashboard to view notifications and mark them as read

### 🌓 Dark Mode
- Full support for dark theme across all pages
- Toggle button in the navbar for seamless theme switching
- System preference auto-detection (prefers-color-scheme)
- Persistent preference saved in localStorage (with zero-flash page reloads)

### 🗂️ Left Sidebar Layout
- Structured left sidebar for desktop and tablet screens
- Displays authenticated user information (avatar, name, email)
- Quick links for Feed, Messages, Notifications, Profile, and Settings
- Expandable placeholder block for future widgets and feature additions
- Responsive design: automatically hidden on mobile views to maximize space

### 📖 Stories
- Publish stories (images with optional short captions) visible to followed users
- Active for 24 hours before automatic expiration
- Horizontal scrollable Stories bar at the top of the feed page
- Full-screen interactive slideshow viewer with autoplay progress bars, skip navigation, and owner delete actions

### 🖼️ Image Cropping
- **Smart crop tool for all image uploads** with real-time preview
- **Posts**: Crop photos to square (1:1) format before publishing
- **Stories**: Crop photos to portrait (9:16) format before uploading
- **Profile Avatar**: Crop and adjust avatar with modal preview (1:1 square)
- **Profile Cover**: Crop and adjust cover photo with modal preview (3:1 landscape)
- Interactive cropper with drag-to-move and resize-to-adjust controls
- Automatic image optimization and conversion to JPEG (90% quality)
- Supports all common image formats (JPG, PNG, GIF, WebP)
- Maximum file size: 5MB per image
- For detailed implementation, see [IMAGE_CROPPING_GUIDE.md](IMAGE_CROPPING_GUIDE.md)

### 💬 Floating Chat Widget
- Circular floating chat trigger button on all pages with dynamic unread count badge
- Slide-up panel with Inbox and Contacts tabs
- Inbox: Displays active chat partners with avatar, name, last message preview, and time
- Contacts: Lists followed users to easily start new chat threads
- Active Chat: Scrollable per-user chat feed with styled bubbles (left/received, right/sent) and instant send form
- Background Auto-polling: Background sync fetches new messages every 5 seconds to provide simulated real-time chat updates

### 🛡️ Admin Panel
- Restrictive route middleware (`admin`) ensuring only users with `is_admin = true` can access administration pages.
- Sidebar link to "Admin Panel" visible in the main app navigation layout for admins only.
- Comprehensive Dashboard with platform-wide aggregations (total users, posts, stories, admins, banned users, new sign-ups this week) and tables displaying the latest 5 registered users and 5 recent posts.
- Searchable User Management table allowing admins to ban/unban (soft delete/restore) users, promote/demote administrators, and permanently delete accounts.
- Searchable Post Moderation dashboard allowing admins to view, audit, and permanently delete any post.
- Grid-based Story Moderation dashboard to audit active/expired stories and delete inappropriate ones.

### ⚡ Performance Optimization
- **Eager Loading & N+1 Prevention**: Systematically implemented Eloquent eager loading for post creators, likes, comment authors, and follow relationships to prevent database N+1 query loops.
- **Grouped lookups**: Batched chat latest messages, unread counts, and notifications performer details in single grouped queries, reducing database load to a minimum regardless of list size.
- **Pre-counted Attributes**: Replaced resource-intensive counts by leveraging `withCount` and `loadCount` database-level aggregates for followers, following, and posts counts.
- **Route Model Binding Optimization**: Restructured user route resolving to execute database-level searches using indexed lookups, preventing memory-exhausting `User::all()` sweeps.

### #️⃣ Hashtags & Mentions
- Parsing of `#hashtag` and `@username` inside post bodies to generate links dynamically.
- Cache-based user mention lookup to prevent performance regressions.
- Automated `UserMentionedNotification` database records and real-time WebSocket notifications sent to mentioned users.
- Redirection of hashtag searches in the navbar to feed tag filtering.

### 💎 Liquid Glass UI (Theme-Adaptive Specular Glassmorphism)
- **High-Fidelity Glassmorphism**: Cards and headers across Feed, Profiles, Search, and Notifications are styled as semi-translucent, refractive glass elements.
- **Specular Glare Tracking**: Integrated haptic tracker engine dynamically recalculates light reflection vectors (`--mouse-x`, `--mouse-y`) as the cursor moves across cards, creating a 3D specular glare effect.
- **Vibrant Mesh Backdrops**: Infused ambient purple, emerald, and blue blur blobs behind layout panels to provide visual depth for optimal glass refractiveness.
- **Theme-Adaptive Custom Properties**: Seamless automatic adaptation to Light and Dark themes, adjusting backdrop opacity, saturation factors (160% vs. 120%), and glare blending modes (overlay vs. screen).
- **Accessibility Fallbacks**: Auto-detection of `prefers-reduced-motion` to substitute GPU-intensive SVG displacement maps and tracking glare with static blurs on low-power devices.

### 📱 Mobile Responsive Optimization
- Sticky bottom navigation bar for mobile viewports displaying Feed, Search, Chats, Alerts, and Profile.
- Native-like app behavior with unread notification badge count indicators on mobile links.
- Bottom padding adjustments and repositioned floating chat elements preventing layouts overlapping.


## Tech Stack

- **Backend:** Laravel 11
- **Frontend:** Blade Templates + Tailwind CSS + Alpine.js
- **Database:** MySQL
- **File Storage:** Local (public disk)

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL

### Setup Steps

1. **Clone and navigate to the project**
   ```bash
   cd c:\projects\socialmedia
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Create the database**
   Create a MySQL database named `socialmedia`:
   ```sql
   CREATE DATABASE socialmedia;
   ```

4. **Update environment file**
   The `.env` file is already configured for MySQL:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=socialmedia
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   Update `DB_USERNAME` and `DB_PASSWORD` if needed.

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Link storage**
   (Already done, but if needed):
   ```bash
   php artisan storage:link
   ```

7. **Build assets**
   ```bash
   npm run build
   ```
   Or for development with hot reload:
   ```bash
   npm run dev
   ```

8. **Start the services (in separate terminal windows)**
   * **Development Web Server**:
     ```bash
     php artisan serve
     ```
   * **WebSocket Server (Reverb)**:
     ```bash
     php artisan reverb:start
     ```
   * **Queue Listener** (processes broadcasts asynchronously):
     ```bash
     php artisan queue:listen
     ```

9. **Visit the app**
   Open [http://localhost:8000](http://localhost:8000) in your browser.

## Usage

### Getting Started
1. Register a new account
2. Complete your profile (Settings → Edit Profile)
3. Upload avatar and cover photo
4. Find users and follow them
5. Create your first post
6. Chat with other users

### Directory Structure

```
app/
├── Http/Controllers/
│   ├── ChatController.php       # Messaging
│   ├── CommentController.php    # Post comments
│   ├── FeedController.php       # Main feed
│   ├── FollowController.php     # Follow/unfollow
│   ├── LikeController.php       # Like posts
│   ├── PostController.php       # Create/delete posts
│   ├── ProfileController.php    # User profiles
│   └── SearchController.php     # User search
├── Models/
│   ├── User.php
│   ├── Profile.php
│   ├── Post.php
│   ├── Comment.php
│   ├── Like.php
│   ├── Follow.php
│   └── Message.php
└── Observers/
    └── UserObserver.php         # Auto-create profiles

resources/views/
├── auth/                        # Login/register
├── feed/                        # Main feed view
├── profile/                     # Profile pages
├── chat/                        # Messaging UI
├── search/                      # Search results
├── components/                  # Reusable components
└── layouts/                     # Base layout

routes/
└── web.php                      # All routes
```

## Database Schema

### Tables
- `users` - User accounts
- `profiles` - Extended user info (bio, avatar, etc.)
- `posts` - User posts with optional images
- `comments` - Comments on posts
- `likes` - Post likes
- `follows` - User follow relationships
- `messages` - Direct messages between users
- `stories` - User 24-hour stories

## Future Enhancements

Potential features to add:
- Video uploads
- Post privacy settings
- Block/report users

## License

Open-source. Feel free to use and modify.


