<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('layout.welcome');
})->name('welcome');

Route::get('/home', function () {
    return view('layout.home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
