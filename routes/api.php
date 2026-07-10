<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController as ApiPostController;
use App\Http\Controllers\Api\CommentController as ApiCommentController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/user', [AuthController::class, 'me']);

Route::get('/posts', [ApiPostController::class, 'index']);
Route::get('/posts/{post}', [ApiPostController::class, 'show']);
Route::post('/posts', [ApiPostController::class, 'store']);
Route::put('/posts/{post}', [ApiPostController::class, 'update']);
Route::delete('/posts/{post}', [ApiPostController::class, 'destroy']);

Route::post('/posts/{post}/comments', [ApiCommentController::class, 'store']);
Route::put('/comments/{comment}', [ApiCommentController::class, 'update']);
Route::delete('/comments/{comment}', [ApiCommentController::class, 'destroy']);

Route::get('/categories', [ApiCategoryController::class, 'index']);
Route::get('/categories/{category}', [ApiCategoryController::class, 'show']);
