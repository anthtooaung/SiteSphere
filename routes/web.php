<?php

use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\FaviconController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostsController;
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
});

Route::post('/contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
