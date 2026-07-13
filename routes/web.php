<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ThesisController;
use App\Http\Controllers\ReplayAttackController;
use App\Http\Controllers\SessionRegisterController;
use App\Http\Controllers\TokenRegisterController;
use App\Http\Controllers\StorageDashboardController;

// Authentication Routes
Auth::routes();

// Redirect home to posts index
Route::get('/', [PostController::class, 'index'])->name('home');
Route::get('/home', [PostController::class, 'index'])->name('home');

// Post Routes
Route::resource('posts', PostController::class);

// Category Routes
Route::resource('categories', CategoryController::class)->only(['index', 'show']);

// Comment Routes (nested under posts)
Route::post('posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
Route::put('comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

// API documentation for token-based access
Route::view('/api-docs', 'api-docs')->name('api.docs');
Route::view('/session-login', 'session-login')->name('session.login');
Route::view('/token-login', 'token-login')->name('token.login');
Route::view('/token-demo', 'token-demo')->name('token.demo');
Route::view('/dashboard/scalability', 'dashboard.scalability')->name('dashboard.scalability');
Route::get('/dashboard/storage', [StorageDashboardController::class, 'index'])->name('dashboard.storage');
Route::view('/dashboard/security', 'dashboard.security')->name('dashboard.security');
Route::get('/comparison', [ThesisController::class, 'comparison'])->name('comparison.dashboard');
Route::view('/presentation-summary', 'presentation-summary')->name('presentation.summary');
Route::view('/features', 'features')->name('forum.features');

Route::get('/thesis/questions', [ThesisController::class, 'questions'])->name('thesis.questions');
Route::get('/thesis/methodology', [ThesisController::class, 'methodology'])->name('thesis.methodology');
Route::get('/thesis/experiment', [ThesisController::class, 'experiment'])->name('thesis.experiment');
Route::get('/thesis/security', [ThesisController::class, 'security'])->name('thesis.security');
Route::get('/thesis/replay-attack', [ReplayAttackController::class, 'demo'])->name('thesis.replay');
Route::post('/thesis/replay/token', [ReplayAttackController::class, 'testTokenReplay'])->name('thesis.replay.token');
Route::post('/thesis/replay/session', [ReplayAttackController::class, 'testSessionReplay'])->name('thesis.replay.session');
Route::get('/thesis/replay/session-info', [ReplayAttackController::class, 'mySessionInfo'])
    ->middleware('auth')
    ->name('thesis.replay.session-info');
Route::get('/thesis/complexity', [ThesisController::class, 'complexity'])->name('thesis.complexity');
Route::get('/thesis/data', [ThesisController::class, 'data'])->name('thesis.data');

// Registration flows for storage experiments
Route::get('/session-register', [SessionRegisterController::class, 'showForm'])->name('session.register');
Route::post('/session-register', [SessionRegisterController::class, 'register']);

Route::get('/token-register', [TokenRegisterController::class, 'showForm'])->name('token.register');
Route::post('/token-register', [TokenRegisterController::class, 'register']);
