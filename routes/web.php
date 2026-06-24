<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\AdminActivityLogController;
use App\Http\Controllers\AdminReportsController;
use App\Http\Controllers\AdminUsersController;
use App\Http\Controllers\AppearanceController;
use App\Http\Controllers\BookmarksController;
use App\Http\Controllers\CommentReactionsController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DashboardController;
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
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/favicon.svg', FaviconController::class)->name('favicon');

Route::get('/', WelcomeController::class)->name('welcome');
Route::get('/about', AboutUsController::class)->name('about-us');

// home doesn't need to use auth it will show all
Route::get('/home', HomeController::class)->name('home');

Route::middleware('auth')->group(function (): void {
    Route::get('/posts/create', [PostsController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostsController::class, 'store'])->name('posts.store');
    Route::patch('/posts/{post}/description', [PostsController::class, 'updateDescription'])->name('posts.description.update');
    Route::delete('/posts/{post}/description', [PostsController::class, 'destroyDescription'])->name('posts.description.destroy');
    Route::post('/posts/{post}/bookmark', [BookmarksController::class, 'store'])->name('posts.bookmark');
    Route::post('/posts/{post}/report', [ReportsController::class, 'store'])->name('posts.report');
    Route::post('/posts/{post}/unban', [PostsController::class, 'unban'])->name('posts.unban');
    Route::post('/posts/{post}/toggle-unsecure', [PostsController::class, 'toggleUnsecure'])->name('posts.toggle-unsecure');
    Route::delete('/posts/{post}/force-delete', [PostsController::class, 'forceDelete'])->withTrashed()->name('posts.force-delete');
    Route::delete('/audits/{userPost}/delete', [PostsController::class, 'deleteAudit'])->name('audits.delete');
    Route::delete('/audits/{userPost}/force-delete', [PostsController::class, 'forceDeleteAudit'])->withTrashed()->name('audits.force-delete');
    Route::post('/user-posts/{userPost}/report', [ReportsController::class, 'storeForUserPost'])->name('user-posts.report');

    Route::post('/posts/{posts:slug}/comments', [CommentsController::class, 'store'])->name('posts.comments.store');
    Route::patch('/comments/{comment}', [CommentsController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentsController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/react', [CommentReactionsController::class, 'toggle'])->name('comments.react');
    Route::post('/comments/{comment}/report', [ReportsController::class, 'storeForComment'])->name('comments.report');
    Route::delete('/comments/{comment}/delete', [CommentsController::class, 'delete'])->name('comments.delete');
    Route::delete('/comments/{comment}/force-delete', [CommentsController::class, 'forceDelete'])->withTrashed()->name('comments.force-delete');
});

Route::get('/posts/{posts:slug}', [PostsController::class, 'show'])->name('posts.show');
Route::get('/users/{user}/hover-card', UserHoverCardController::class)->name('users.hover-card');

Route::post('/contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::middleware('auth')->group(function (): void {
    Route::get('/menu/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile/{slug?}', ProfileDetailController::class)->name('profile-detail');
    Route::post('/users/{user}/report', [ReportsController::class, 'storeForUser'])->name('users.report');

    Route::get('/menu/edit-profile', [EditProfileController::class, 'edit'])->name('edit-profile');
    Route::patch('/menu/edit-profile', [EditProfileController::class, 'update'])->name('edit-profile.update');

    Route::get('/menu/edit-tag', [EditTagsController::class, 'index'])->name('edit-tag');
    Route::patch('/menu/edit-tag', [EditTagsController::class, 'update'])->name('edit-tag.update');
    Route::delete('/menu/edit-tag/custom-tags', [EditTagsController::class, 'reset'])->name('edit-tag.reset');

    Route::get('/menu/saved-post', SavedPostsController::class)->name('saved-post');

    Route::get('/menu/reports', [AdminReportsController::class, 'index'])->name('reports');
    Route::patch('/menu/reports/{report}/read', [AdminReportsController::class, 'markRead'])->name('reports.read');
    Route::patch('/menu/reports/{report}/unread', [AdminReportsController::class, 'markUnread'])->name('reports.unread');
    Route::get('/menu/reports/{report}/open', [AdminReportsController::class, 'open'])->name('reports.open');
    Route::delete('/menu/reports/{report}/resolve', [AdminReportsController::class, 'resolve'])->name('reports.resolve');

    Route::get('/menu/appearance', [AppearanceController::class, 'index'])->name('appearance');
    Route::patch('/menu/appearance', [AppearanceController::class, 'update'])->name('appearance.update');

    Route::get('/menu/security', [SecurityController::class, 'edit'])->name('security');
    Route::patch('/menu/security', [SecurityController::class, 'update'])->name('security.update');

    Route::get('/menu/users', [AdminUsersController::class, 'index'])->name('users');
    Route::patch('/menu/users/{user}/role', [AdminUsersController::class, 'updateRole'])->name('users.role');
    Route::delete('/menu/users/{user}', [AdminUsersController::class, 'destroy'])->name('users.destroy');
    Route::patch('/menu/users/{user}/restore', [AdminUsersController::class, 'restore'])->withTrashed()->name('users.restore');
    Route::delete('/menu/users/{user}/force-delete', [AdminUsersController::class, 'forceDelete'])->withTrashed()->name('users.force-delete');
    Route::post('/menu/users/{user}/toggle-unsecure', [AdminUsersController::class, 'toggleUnsecure'])->name('users.toggle-unsecure');

    Route::get('/menu/dashboard/activity-log', [AdminActivityLogController::class, 'index'])->name('admin.activity-log');
    Route::get('/api/admin/activity/{date}', [AdminActivityLogController::class, 'show'])->name('admin.activity-date');

    Route::post('/notifications/{notification}/open', NotificationOpenController::class)->name('notifications.open');
    Route::post('/notifications/mark-all-read', [NotificationOpenController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});

require __DIR__.'/auth.php';
