<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PreferenceController;
use Illuminate\Support\Facades\Route;

// Public Routes
// Articles
Route::get('/articles', [ArticleController::class, 'index']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Preferences
    Route::get('/preferences', [PreferenceController::class, 'index']);
    Route::post('/preferences', [PreferenceController::class, 'store']);
    Route::put('/preferences/{id}', [PreferenceController::class, 'update']);
    Route::delete('/preferences/{id}', [PreferenceController::class, 'destroy']);

    // Fetch articles based on user preferences
    Route::get('/articles/preferred', [ArticleController::class, 'fetchByPreference']);
});