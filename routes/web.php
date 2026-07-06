<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\PrayerTimeController;
use Illuminate\Support\Facades\Route;

// Redirect root to feed or login
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('feed')
        : redirect()->route('login');
});

// Auth routes (Breeze)
require __DIR__ . '/auth.php';

// Public image-serving routes (no auth needed — browser img tags can't send session cookies)
Route::get('/@{user}/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');
Route::get('/@{user}/cover', [ProfileController::class, 'cover'])->name('profile.cover');

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Feed
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');

    // Search
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::get('/api/following/search', [SearchController::class, 'searchFollowing']);

    // Posts
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Likes
    Route::post('/posts/{post}/like', [LikeController::class, 'toggle'])->name('posts.like');

    // Comments
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Profiles
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/@{user}', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/@{user}/followers', [ProfileController::class, 'followers'])->name('profile.followers');
    Route::get('/@{user}/following', [ProfileController::class, 'following'])->name('profile.following');

    // Follow / Unfollow
    Route::post('/@{user}/follow', [FollowController::class, 'toggle'])->name('follow.toggle');

    // Stories
    Route::post('/stories', [StoryController::class, 'store'])->name('stories.store');
    Route::delete('/stories/{story}', [StoryController::class, 'destroy'])->name('stories.destroy');

    // Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{user}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{user}', [ChatController::class, 'send'])->name('chat.send');

    // Chat APIs for Floating Chat widget
    Route::get('/api/chat/conversations', [ChatController::class, 'apiGetConversations']);
    Route::get('/api/chat/contacts', [ChatController::class, 'apiGetContacts']);
    Route::get('/api/chat/messages/{user:id}', [ChatController::class, 'apiGetMessages']);
    Route::post('/api/chat/messages/{user:id}', [ChatController::class, 'apiSendMessage']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/{id}/go', [NotificationController::class, 'goAndMarkAsRead'])->name('notifications.go');
    Route::get('/api/notifications/unread', [NotificationController::class, 'apiGetUnread']);

    // Prayer Times API
    Route::get('/api/prayer-times', [PrayerTimeController::class, 'getTimes']);
    Route::get('/api/prayer-times/zones', [PrayerTimeController::class, 'getZones']);

    // Dashboard redirect → feed
    Route::get('/dashboard', fn() => redirect()->route('feed'))->name('dashboard');
});

// Admin Panel Routes (auth + admin middleware)
use App\Http\Controllers\Admin\AdminController;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',               [AdminController::class, 'dashboard'])->name('dashboard');

    // Users
    Route::get('/users',          [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/ban',     [AdminController::class, 'banUser'])->name('users.ban');
    Route::post('/users/{id}/unban',     [AdminController::class, 'unbanUser'])->name('users.unban');
    Route::post('/users/{user}/promote', [AdminController::class, 'promoteUser'])->name('users.promote');
    Route::post('/users/{user}/demote',  [AdminController::class, 'demoteUser'])->name('users.demote');
    Route::delete('/users/{id}/delete',  [AdminController::class, 'deleteUser'])->name('users.delete');

    // Posts
    Route::get('/posts',          [AdminController::class, 'posts'])->name('posts');
    Route::delete('/posts/{post}', [AdminController::class, 'deletePost'])->name('posts.delete');

    // Stories
    Route::get('/stories',        [AdminController::class, 'stories'])->name('stories');
    Route::delete('/stories/{story}', [AdminController::class, 'deleteStory'])->name('stories.delete');
});
