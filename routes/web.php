<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/chat', 'chat')->name('chat');
    Route::view('/dashboard', 'dashboard')->name('dashboard');
});

require __DIR__ . '/auth.php';
