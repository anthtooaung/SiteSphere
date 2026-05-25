<?php

use App\Http\Controllers\ContactMessageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('layout.welcome');
})->name('welcome');

Route::get('/home', function () {
    return view('layout.home');
})->name('home');

Route::post('/contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
