<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('tasks', 'tasks')->name('tasks.index');
    Route::view('employees', 'employees')->name('employees.index');
    Route::view('hierarchy', 'hierarchy')->name('hierarchy.index');
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
