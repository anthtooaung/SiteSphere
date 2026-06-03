<?php

use App\Http\Controllers\BookmarksController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\FaviconController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\ReportsController;
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
});

Route::post('/contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::middleware('auth')->group(function (): void {
    Route::get('/menu/dashboard', function () {
        return view('layout.menu.dashboard');
    })->name('dashboard');

    Route::get('/menu/edit-profile', function () {
        return view('layout.menu.edit-profile');
    })->name('edit-profile');

    Route::get('/menu/saved-post', function () {
        return view('layout.menu.saved-post');
    })->name('saved-post');

    Route::get('/menu/reports', function () {
        return view('layout.menu.reports');
    })->name('reports');

    Route::get('/menu/security', function () {
        return view('layout.menu.security');
    })->name('security');

    Route::get('/menu/users', function () {
        return view('layout.menu.users');
    })->name('users');
});

require __DIR__.'/auth.php';
