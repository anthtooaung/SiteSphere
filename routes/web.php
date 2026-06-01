<?php

use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\FaviconController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/favicon.svg', FaviconController::class)->name('favicon');

Route::get('/', function () {
    return view('layout.welcome');
})->name('welcome');

Route::get('/home', HomeController::class)->middleware('auth')->name('home');

Route::post('/contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
