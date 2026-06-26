<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
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
Route::get('/users/{user}/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');
Route::get('/users/{user}/cover', [ProfileController::class, 'cover'])->name('profile.cover');

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Feed
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');

    // Search
    Route::get('/search', [SearchController::class, 'index'])->name('search');

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
    Route::get('/users/{user}', [ProfileController::class, 'show'])->name('profile.show');

    // Follow / Unfollow
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->name('follow.toggle');

    // Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{user}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{user}', [ChatController::class, 'send'])->name('chat.send');

    // Dashboard redirect → feed
    Route::get('/dashboard', fn() => redirect()->route('feed'))->name('dashboard');
});
