<?php

use App\Http\Controllers\GitUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('tasks', 'tasks')->name('tasks.index');
    Route::view('hierarchy', 'hierarchy')->name('hierarchy.index');
    Route::view('profile', 'profile')->name('profile');

    // Git Self-Updater Routes
    Route::get('/git-info', [GitUpdateController::class, 'info'])->name('git.info');
    Route::post('/git-update', [GitUpdateController::class, 'update'])->name('git.update');
});

require __DIR__.'/auth.php';
