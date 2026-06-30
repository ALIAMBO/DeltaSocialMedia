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
- Dynamic unread notification count badge in navbar
- Dedicated dashboard to view notifications and mark them as read

### 🌓 Dark Mode
- Full support for dark theme across all pages
- Toggle button in the navbar for seamless theme switching
- System preference auto-detection (prefers-color-scheme)
- Persistent preference saved in localStorage (with zero-flash page reloads)

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

## Future Enhancements

Potential features to add:
- Real-time updates with Pusher/WebSockets
- Post sharing/reposting
- Hashtags and mentions
- Stories feature
- Video uploads
- Admin panel
- Post privacy settings
- Block/report users

## License

Open-source. Feel free to use and modify.


