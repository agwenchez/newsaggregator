<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ScrappingController;
use App\Http\Controllers\SourceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Articles
// Route::get('/articles/filter', [ArticleController::class,'filterArticles']);
Route::get('/articles/filter', [ArticleController::class, 'filter']);
Route::get('/articles/search', [ArticleController::class, 'search']);
// Source Routes
Route::get('/sources', [SourceController::class, 'index']);
Route::post('/sources', [SourceController::class, 'store']);
Route::put('/sources/{id}', [SourceController::class, 'update']);
Route::delete('/sources/{id}', [SourceController::class, 'destroy']);

// Category Routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
