<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\CategoryController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/verify-email', [AuthController::class, 'verifyEmail']);

Route::middleware('throttle:6,1')->group(function () { // 6 requests per minute
    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);
});


Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);
});

Route::middleware('auth:api')->group(function () {
    Route::apiResource('tasks', TaskController::class);

    Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete']);
    Route::get('/tasks/trashed', [TaskController::class, 'trashed']); // Untuk melihat task yang di-soft delete
    Route::patch('/tasks/{task}/restore', [TaskController::class, 'restore']); // Restore task

    Route::apiResource('categories', CategoryController::class);
});


