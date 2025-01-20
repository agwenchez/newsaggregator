<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PreferenceController;
use Illuminate\Support\Facades\Route;

// Public Routes
// Articles
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/search', [ArticleController::class, 'search']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Preferences
    Route::get('/preferences', [PreferenceController::class, 'index']); // List preferences
    Route::post('/preferences', [PreferenceController::class, 'store']); // Create preference
    Route::put('/preferences/{id}', [PreferenceController::class, 'update']); // Update preference
    Route::delete('/preferences/{id}', [PreferenceController::class, 'destroy']); // Delete preference

    // Fetch articles based on user preferences
    Route::get('/articles/preferred', [ArticleController::class, 'fetchByPreference']);
});