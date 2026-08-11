<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\PostController;
use App\Models\ExperimentMetric;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ThesisController;
use App\Http\Controllers\ReplayAttackController;
use App\Models\User;

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

Route::post('/session-login', function (Request $request) {
    $credentials = $request->only('email', 'password');
    $remember = (bool) $request->input('remember', false);

    $success = Auth::attempt($credentials, $remember);

    if ($success) {
        $request->session()->regenerate();
        $request->session()->put('victim_authentication_type', 'session');
        $request->session()->put('victim_session_id', $request->session()->getId());
        $request->session()->put('victim_token', null);

        return response()->json([
            'success' => true,
            'message' => 'Session login successful',
            'auth_type' => 'session',
            'user' => [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
            'session_id' => $request->session()->getId(),
            'authenticated' => Auth::check(),
        ], 200);
    }

    return response()->json([
        'success' => false,
        'message' => 'Incorrect email or password. Please try again.',
        'auth_type' => 'session',
        'authenticated' => false,
    ], 422);
})->name('session.login.submit');

Route::view('/token-login', 'token-login')->name('token.login');
Route::post('/token-login-state', function (Request $request) {
    $request->session()->put('victim_authentication_type', 'token');
    $request->session()->put('victim_session_id', null);
    $request->session()->put('victim_token', 'captured-token-' . $request->session()->getId());

    return response()->json(['success' => true, 'auth_type' => 'token']);
})->name('token.login.state');
Route::view('/token-demo', 'token-demo')->name('token.demo');
Route::view('/dashboard/scalability', 'dashboard.scalability')->name('dashboard.scalability');
Route::view('/dashboard/storage', 'dashboard.storage')->name('dashboard.storage');
Route::view('/dashboard/security', 'dashboard.security')->name('dashboard.security');
Route::get('/comparison', [ThesisController::class, 'comparison'])->name('comparison.dashboard');
Route::view('/presentation-summary', 'presentation-summary')->name('presentation.summary');
Route::view('/features', 'features')->name('forum.features');

Route::get('/thesis/questions', [ThesisController::class, 'questions'])->name('thesis.questions');
Route::get('/thesis/methodology', [ThesisController::class, 'methodology'])->name('thesis.methodology');
Route::get('/thesis/experiment', [ThesisController::class, 'experiment'])->name('thesis.experiment');
Route::get('/thesis/security', [ThesisController::class, 'security'])->name('thesis.security');
Route::get('/thesis/replay-attack', [ReplayAttackController::class, 'demo'])->name('thesis.replay');
Route::post('/thesis/replay/compromise', [ReplayAttackController::class, 'compromise'])->name('thesis.replay.compromise');
Route::post('/thesis/replay/token', [ReplayAttackController::class, 'testTokenReplay'])->name('thesis.replay.token');
Route::post('/thesis/replay/session', [ReplayAttackController::class, 'testSessionReplay'])->name('thesis.replay.session');
Route::get('/thesis/replay/session-info', [ReplayAttackController::class, 'mySessionInfo'])
    ->middleware('auth')
    ->name('thesis.replay.session-info');
Route::get('/thesis/complexity', [ThesisController::class, 'complexity'])->name('thesis.complexity');
Route::get('/thesis/data', [ThesisController::class, 'data'])->name('thesis.data');

Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::view('/dashboard/revocation-latency', 'dashboard.revocation-latency')->name('dashboard.revocation-latency');
Route::view('/dashboard/data-exposure-risk', 'dashboard.data-exposure-risk')->name('dashboard.data-exposure-risk');

Route::post('/victim/logout', function (Request $request) {
    if (! Auth::check()) {
        return response()->json(['success' => false, 'message' => 'Unauthenticated.']);
    }

    $logoutTime = now();
    $request->session()->put('victim_logout_time', $logoutTime->toDateTimeString());

    if (config('session.driver') === 'database') {
        DB::table(config('session.table'))
            ->where('id', $request->session()->getId())
            ->delete();
    }

    $latestMetric = ExperimentMetric::where('victim_id', Auth::id())
        ->where('action', 'link_clicked')
        ->whereNull('logout_time')
        ->orderBy('created_at', 'desc')
        ->first();

    if ($latestMetric) {
        $latestMetric->logout_time = $logoutTime;
        $latestMetric->save();
    }

    return response()->json([
        'success' => true,
        'logout_time' => $logoutTime->toIso8601String(),
    ]);
})->name('victim.logout');

Route::post('/dashboard/revocation-latency/validate-session', function (Request $request) {
    $sessionId = $request->input('session_id');

    $sessionExists = false;
    if (config('session.driver') === 'database') {
        $sessionExists = DB::table(config('session.table'))->where('id', $sessionId)->exists();
    }

    $logoutMetric = ExperimentMetric::where('victim_session_id', $sessionId)
        ->where('action', 'link_clicked')
        ->whereNotNull('logout_time')
        ->orderBy('created_at', 'desc')
        ->first();

    return response()->json([
        'success' => true,
        'valid' => $sessionExists,
        'logout_time' => $logoutMetric?->logout_time?->toIso8601String(),
    ]);
})->name('dashboard.revocation-latency.validate.session');

Route::get('/dashboard/revocation-latency/status', function () {
    $latestMetric = ExperimentMetric::where('action', 'link_clicked')
        ->orderBy('created_at', 'desc')
        ->first();

    return response()->json([
        'success' => true,
        'logout_time' => $latestMetric?->logout_time ? $latestMetric->logout_time->toIso8601String() : null,
        'victim_authentication_type' => $latestMetric?->victim_authentication_type,
    ]);
})->name('dashboard.revocation-latency.status');

Route::middleware(['web','auth'])->group(function () {
    Route::get('/phish', function (Request $request) {
        if (Auth::check()) {
            $victimUser = Auth::user();
            $authType = $request->session()->get('victim_authentication_type', 'session');
            $capturedSessionId = null;
            $capturedToken = null;
            $attackerId = $request->query('attacker_id');

            if ($authType === 'session') {
                $capturedSessionId = $request->session()->getId();
            }

            if ($authType === 'token' && $victimUser) {
                $capturedToken = $victimUser->api_token;
            }

            $logoutTime = null;
            if ($authType === 'session' && $request->session()->has('victim_logout_time')) {
                $logoutTime = $request->session()->get('victim_logout_time');
            }

            if (! $attackerId) {
                $attackerUser = User::where('name', 'Aung Kyaw')->first();

                if (! $attackerUser) {
                    $attackerUser = User::where('email', 'aungkyaw@example.com')->first();
                }

                if ($attackerUser) {
                    $attackerId = $attackerUser->id;
                }
            }

            ExperimentMetric::create([
                'auth_type' => 'phish',
                'action' => 'link_clicked',
                'method' => 'GET',
                'path' => $request->path(),
                'duration_ms' => 0,
                'memory_usage' => 0,
                'query_count' => 0,
                'storage_bytes' => 0,
                'success' => true,
                'victim_id' => $victimUser->id,
                'victim_name' => $victimUser->name,
                'victim_email' => $victimUser->email,
                'victim_authentication_type' => $authType,
                'victim_session_id' => $capturedSessionId,
                'victim_token' => $capturedToken,
                'logout_time' => $logoutTime,
                'attacker_id' => $attackerId,
                'victim_user_agent' => $request->header('User-Agent'),
            ]);
        }

        return view('phish');
    })->name('phish');
});
