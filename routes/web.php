<?php

use App\Http\Controllers\AdminReportsController;
use App\Http\Controllers\AdminUsersController;
use App\Http\Controllers\AppearanceController;
use App\Http\Controllers\BookmarksController;
use App\Http\Controllers\CommentReactionsController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\EditProfileController;
use App\Http\Controllers\EditTagsController;
use App\Http\Controllers\FaviconController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationOpenController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\ProfileDetailController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SavedPostsController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\UserHoverCardController;
use Illuminate\Support\Facades\Route;

Route::get('/favicon.svg', FaviconController::class)->name('favicon');

Route::get('/', function () {
    return view('layout.welcome');
})->name('welcome');

// home doesn't need to use auth it will show all
Route::get('/home', HomeController::class)->name('home');

Route::middleware('auth')->group(function (): void {
    Route::get('/posts/create', [PostsController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostsController::class, 'store'])->name('posts.store');
    Route::post('/posts/{post}/bookmark', [BookmarksController::class, 'store'])->name('posts.bookmark');
    Route::post('/posts/{post}/report', [ReportsController::class, 'store'])->name('posts.report');
    Route::post('/posts/{post}/ban', [PostsController::class, 'ban'])->name('posts.ban');

    Route::post('/posts/{posts:slug}/comments', [CommentsController::class, 'store'])->name('posts.comments.store');
    Route::post('/comments/{comment}/react', [CommentReactionsController::class, 'toggle'])->name('comments.react');
});

Route::get('/posts/{posts:slug}', [PostsController::class, 'show'])->name('posts.show');
Route::get('/users/{user}/hover-card', UserHoverCardController::class)->name('users.hover-card');

Route::post('/contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::middleware('auth')->group(function (): void {
    Route::get('/menu/dashboard', function () {
        return view('layout.menu.dashboard');
    })->name('dashboard');

    Route::get('/profile/{slug?}', ProfileDetailController::class)->name('profile-detail');

    Route::get('/menu/edit-profile', [EditProfileController::class, 'edit'])->name('edit-profile');
    Route::patch('/menu/edit-profile', [EditProfileController::class, 'update'])->name('edit-profile.update');

    Route::get('/menu/edit-tag', [EditTagsController::class, 'index'])->name('edit-tag');
    Route::patch('/menu/edit-tag', [EditTagsController::class, 'update'])->name('edit-tag.update');
    Route::delete('/menu/edit-tag/custom-tags', [EditTagsController::class, 'reset'])->name('edit-tag.reset');

    Route::get('/menu/saved-post', SavedPostsController::class)->name('saved-post');

    Route::get('/menu/reports', [AdminReportsController::class, 'index'])->name('reports');
    Route::patch('/menu/reports/{report}/read', [AdminReportsController::class, 'markRead'])->name('reports.read');
    Route::patch('/menu/reports/{report}/unread', [AdminReportsController::class, 'markUnread'])->name('reports.unread');
    Route::delete('/menu/reports/post/{id}', [AdminReportsController::class, 'deletePost'])->name('reports.delete.post');
    Route::delete('/menu/reports/comment/{id}', [AdminReportsController::class, 'deleteComment'])->name('reports.delete.comment');

    Route::get('/menu/appearance', [AppearanceController::class, 'index'])->name('appearance');
    Route::patch('/menu/appearance', [AppearanceController::class, 'update'])->name('appearance.update');

    Route::get('/menu/security', [SecurityController::class, 'edit'])->name('security');
    Route::patch('/menu/security', [SecurityController::class, 'update'])->name('security.update');

    Route::get('/menu/users', [AdminUsersController::class, 'index'])->name('users');
    Route::patch('/menu/users/{user}/role', [AdminUsersController::class, 'updateRole'])->name('users.role');
    Route::delete('/menu/users/{user}', [AdminUsersController::class, 'destroy'])->name('users.destroy');
    Route::patch('/menu/users/{user}/restore', [AdminUsersController::class, 'restore'])->withTrashed()->name('users.restore');

    Route::post('/notifications/{notification}/open', NotificationOpenController::class)->name('notifications.open');
});

require __DIR__.'/auth.php';
