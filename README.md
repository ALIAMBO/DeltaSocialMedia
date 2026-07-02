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
- View followers & following counts
- Follow/unfollow users
- Profile link in avatar dropdown menu (navbar)

### 📝 Posts & Feed
- Create posts with text and/or images
- View feed from followed users
- Like and comment on posts
- Delete own posts and comments

### 💬 Real-time Messaging
- Chat with other users
- View all conversations
- Message read status
- Conversation list sorted by last message

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

8. **Start the development server**
   ```bash
   php artisan serve
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
- Real-time updates with Pusher/WebSockets
- Post sharing/reposting
- Hashtags and mentions
- Video uploads
- Admin panel
- Post privacy settings
- Block/report users

## License

Open-source. Feel free to use and modify.


