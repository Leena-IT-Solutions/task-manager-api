<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HierarchyController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Sanctum Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Employee Directory & Hierarchy
    Route::get('/employees', [HierarchyController::class, 'listEmployees']);
    Route::post('/employees/lookup', [HierarchyController::class, 'lookupEmployee']);
    Route::post('/employees/{employee}/assign-manager', [HierarchyController::class, 'assignManager']);
    Route::get('/hierarchy', [HierarchyController::class, 'teamTree']);
    Route::get('/assignable-employees', [HierarchyController::class, 'assignableEmployees']);

    // Tasks API
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::post('/tasks/{task}/comments', [TaskController::class, 'addComment']);
});
