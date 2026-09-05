<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\PostController;
use App\Models\ExperimentMetric;
use App\Models\AuthSecurityEvent;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ThesisController;
use App\Http\Controllers\ReplayAttackController;
use App\Models\User;
use App\Support\JwtHelper;
use App\Support\RevocationLatencyResults;
use App\Support\RevocationLatencyStore;
use App\Support\AttackSuccessRateResults;
use App\Services\CredentialExposureAnalyzer;
use Illuminate\Support\Carbon;

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

Route::view('/analysis-login', 'analysis-login')->name('analysis.login');
Route::post('/analysis-login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $remember = (bool) $request->boolean('remember', false);
    $success = Auth::attempt($credentials, $remember);

    if (! $success) {
        return response()->json([
            'success' => false,
            'message' => 'Incorrect email or password. Please try again.',
        ], 422);
    }

    if (Auth::user()?->role !== 'analysis') {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => false,
            'message' => 'This login is for analysis users only.',
        ], 403);
    }

    $request->session()->regenerate();

    return response()->json([
        'success' => true,
        'message' => 'Analysis login successful',
        'redirect' => route('dashboard.revocation-latency'),
    ]);
})->name('analysis.login.submit');

Route::post('/session-login', function (Request $request) {
    $credentials = $request->only('email', 'password');
    $remember = (bool) $request->input('remember', false);

    $success = Auth::attempt($credentials, $remember);

    if ($success) {
        $request->session()->regenerate();
        $request->session()->put('victim_authentication_type', 'session');
        $request->session()->put('victim_session_id', $request->session()->getId());
        $request->session()->put('victim_token', null);

        RevocationLatencyStore::recordSessionLogin($request->session()->getId(), now()->toIso8601String());

        AuthSecurityEvent::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->delete();

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
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('csrf.token');
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
Route::get('/dashboard', function () {
    if (! Auth::check()) {
        return redirect()->route('analysis.login');
    }

    if (Auth::user()->role !== 'analysis') {
        return redirect()->route('analysis.login');
    }

    return redirect()->route('dashboard.revocation-latency');
})->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/revocation-latency', function () {
        if (Auth::user()->role !== 'analysis') {
            abort(403, 'Access denied. Only analysis users can manage the Security Testing Dashboard.');
        }

        return view('dashboard.revocation-latency');
    })->name('dashboard.revocation-latency');

    Route::get('/dashboard/attack-success-rate', function () {
        if (Auth::user()->role !== 'analysis') {
            abort(403, 'Access denied. Only analysis users can manage the Security Testing Dashboard.');
        }

        return view('dashboard.attack-success-rate');
    })->name('dashboard.attack-success-rate');

    Route::get('/dashboard/data-exposure-risk', function () {
        if (Auth::user()->role !== 'analysis') {
            abort(403, 'Access denied. Only analysis users can manage the Security Testing Dashboard.');
        }

        return view('dashboard.data-exposure-risk');
    })->name('dashboard.data-exposure-risk');
});
Route::post('/dashboard/revocation-latency/reset-captured-credentials', function () {
    ExperimentMetric::where('auth_type', 'phish')
        ->where('action', 'link_clicked')
        ->delete();

    AuthSecurityEvent::where('status', 'pending')
        ->delete();

    return response()->json([
        'success' => true,
        'message' => 'Captured phishing credentials and pending alerts cleared.',
    ]);
})->name('dashboard.revocation-latency.reset-captured-credentials');
Route::post('/dashboard/revocation-latency/security-alert/clear-on-login', function () {
    if (! Auth::check()) {
        return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
    }

    AuthSecurityEvent::where('user_id', Auth::id())
        ->where('status', 'pending')
        ->delete();

    return response()->json(['success' => true]);
})->middleware('auth')->name('dashboard.revocation-latency.security-alert.clear-on-login');
Route::post('/dashboard/revocation-latency/security-alert', function (Request $request) {
    $validated = $request->validate([
        'type' => 'required|string|in:session,token',
        'message' => 'nullable|string',
        'payload' => 'nullable|array',
    ]);

    $payload = $validated['payload'] ?? [];
    $userId = null;

    if ($validated['type'] === 'session' && ! empty($payload['session_id'] ?? null)) {
        $sessionRow = DB::table('sessions')->where('id', (string) $payload['session_id'])->first();
        $userId = $sessionRow?->user_id;
    }

    if ($validated['type'] === 'token' && ! empty($payload['token'] ?? null)) {
        $token = (string) $payload['token'];
        $tokenUser = User::where('api_token', $token)->first();
        $tokenPayload = JwtHelper::decodePayload($token);
        $userId = $tokenUser?->id ?? ($tokenPayload['sub'] ?? null);
    }

    if (! $userId) {
        return response()->json([
            'success' => false,
            'message' => 'Unable to identify the account owner for this credential.',
        ], 422);
    }

    $event = AuthSecurityEvent::create([
        'user_id' => (int) $userId,
        'type' => $validated['type'],
        'status' => 'pending',
        'message' => $validated['message'] ?? 'Someone is trying to use your account. So if it is not you, please logout of all devices',
        'payload' => $payload,
    ]);

    return response()->json([
        'success' => true,
        'event_id' => $event->id,
        'user_id' => $event->user_id,
        'message' => $event->message,
    ]);
})->name('dashboard.revocation-latency.security-alert');
Route::get('/dashboard/revocation-latency/security-alert/status', function () {
    if (! Auth::check()) {
        return response()->json(['active' => false]);
    }

    $event = AuthSecurityEvent::where('user_id', Auth::id())
        ->where('status', 'pending')
        ->latest('created_at')
        ->first();

    if (! $event) {
        return response()->json(['active' => false]);
    }

    return response()->json([
        'active' => true,
        'event_id' => $event->id,
        'message' => $event->message,
        'type' => $event->type,
    ]);
})->middleware('auth')->name('dashboard.revocation-latency.security-alert.status');
Route::post('/dashboard/revocation-latency/security-alert/respond', function (Request $request) {
    $validated = $request->validate([
        'event_id' => 'required|integer',
        'action' => 'required|string|in:acknowledge,logout',
    ]);

    $event = AuthSecurityEvent::where('id', $validated['event_id'])
        ->where('user_id', Auth::id())
        ->where('status', 'pending')
        ->first();

    if (! $event) {
        return response()->json(['success' => false, 'message' => 'Security alert not found.'], 404);
    }

    if ($validated['action'] === 'acknowledge') {
        AuthSecurityEvent::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->update(['status' => 'acknowledged']);

        return response()->json(['success' => true, 'action' => 'acknowledged']);
    }

    $payload = $event->payload ?? [];
    $logoutTime = now()->toIso8601String();

    if ($event->type === 'session' && ! empty($payload['session_id'] ?? null)) {
        RevocationLatencyStore::recordSessionLogout((string) $payload['session_id'], $logoutTime);
    }

    if ($event->type === 'token' && ! empty($payload['token'] ?? null)) {
        RevocationLatencyStore::recordTokenLogout((string) $payload['token'], $logoutTime);
    }

    AuthSecurityEvent::where('user_id', Auth::id())
        ->where('status', 'pending')
        ->update(['status' => 'logged_out']);
    $event->update(['status' => 'logged_out']);
    $request->session()->put('victim_logout_time', $logoutTime);

    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    $redirectRoute = $event->type === 'token' ? 'token.login' : 'session.login';

    return response()->json([
        'success' => true,
        'action' => 'logout',
        'type' => $event->type,
        'redirect' => route($redirectRoute),
    ]);
})->middleware('auth')->name('dashboard.revocation-latency.security-alert.respond');
Route::post('/victim/logout', function (Request $request) {
    if (! Auth::check()) {
        return response()->json(['success' => false], 401);
    }

    $authType = $request->session()->get('victim_authentication_type', 'session');
    $logoutTime = now()->toIso8601String();

    AuthSecurityEvent::where('user_id', Auth::id())
        ->where('status', 'pending')
        ->update(['status' => 'logged_out']);

    if ($authType === 'session') {
        RevocationLatencyStore::recordSessionLogout($request->session()->getId(), $logoutTime);
    } else {
        $token = Auth::user()?->api_token;
        if ($token) {
            RevocationLatencyStore::recordTokenLogout($token, $logoutTime);
        }
    }

    return response()->json([
        'success' => true,
        'logout_time' => $logoutTime,
        'type' => $authType,
    ]);
})->middleware('auth')->name('victim.logout');

Route::post('/dashboard/revocation-latency/logout/token', function (Request $request) {
    $token = trim((string) $request->input('token', ''));
    $logoutTime = now()->toIso8601String();

    if ($token !== '') {
        RevocationLatencyStore::recordTokenLogout($token, $logoutTime);
    }

    return response()->json([
        'success' => true,
        'logout_time' => $logoutTime,
        'message' => 'Victim logout recorded. The JWT remains valid until its original expiration time.',
    ]);
})->name('dashboard.revocation-latency.logout.token');
Route::post('/dashboard/revocation-latency/validate/session', function (Request $request) {
    $sessionId = trim((string) $request->input('session_id', ''));

    if ($sessionId === '') {
        return response()->json([
            'valid' => false,
            'expired' => true,
            'logout_occurred' => false,
            'logout_time' => null,
            'token_expiration_time' => null,
            'revocation_latency_seconds' => null,
        ], 422);
    }

    $session = DB::table('sessions')->where('id', $sessionId)->first();
    $isValid = $session && $session->last_activity >= (time() - (config('session.lifetime') * 60));
    $logoutTime = RevocationLatencyStore::getSessionLogoutTime($sessionId);
    $sessionLoginTime = RevocationLatencyStore::getSessionLoginTime($sessionId);

    return response()->json([
        'valid' => $isValid,
        'expired' => ! $isValid,
        'logout_occurred' => ! empty($logoutTime),
        'logout_time' => $logoutTime,
        'session_login_time' => $sessionLoginTime,
        'token_expiration_time' => null,
        'revocation_latency_seconds' => null,
    ]);
})->name('dashboard.revocation-latency.validate.session');
Route::post('/dashboard/revocation-latency/validate/token', function (Request $request) {
    $token = trim((string) $request->input('token', ''));

    if ($token === '') {
        return response()->json([
            'valid' => false,
            'expired' => true,
            'logout_occurred' => false,
            'logout_time' => null,
            'token_expiration_time' => null,
            'revocation_latency_seconds' => null,
        ], 422);
    }

    $payload = JwtHelper::decodePayload($token);
    $exp = $payload['exp'] ?? null;
    $iat = $payload['iat'] ?? null;
    $expired = JwtHelper::isExpired($token) || ! JwtHelper::verifySignature($token) || $payload === null;
    $logoutTime = RevocationLatencyStore::getTokenLogoutTime($token);

    return response()->json([
        'valid' => ! $expired,
        'expired' => $expired,
        'logout_occurred' => ! empty($logoutTime),
        'logout_time' => $logoutTime,
        'token_issued_at' => $iat ? Carbon::createFromTimestamp((int) $iat)->toIso8601String() : null,
        'token_expiration_time' => $exp ? Carbon::createFromTimestamp((int) $exp)->toIso8601String() : null,
        'revocation_latency_seconds' => null,
    ]);
})->name('dashboard.revocation-latency.validate.token');

Route::post('/dashboard/revocation-latency/record-result', function (Request $request) {
    $validated = $request->validate([
        'comparison_id' => 'required|string|min:3|max:60',
        'session_credential' => 'nullable|string',
        'token_credential' => 'nullable|string',
        'session_latency_seconds' => 'nullable|integer|min:0',
        'token_latency_seconds' => 'nullable|integer|min:0',
    ]);

    $recorded = false;

    if ($validated['session_latency_seconds'] !== null && ! empty($validated['session_credential'])) {
        $recorded = RevocationLatencyResults::record(
            'session',
            trim($validated['session_credential']),
            (int) $validated['session_latency_seconds'],
            $validated['comparison_id']
        ) !== null;
    }

    if ($validated['token_latency_seconds'] !== null && ! empty($validated['token_credential'])) {
        $tokenRecorded = RevocationLatencyResults::record(
            'token',
            trim($validated['token_credential']),
            (int) $validated['token_latency_seconds'],
            $validated['comparison_id']
        );
        $recorded = $recorded || $tokenRecorded !== null;
    }

    if (! $recorded) {
        return response()->json([
            'success' => false,
            'message' => 'The captured credentials could not be linked to a tested user.',
        ], 422);
    }

    return response()->json([
        'success' => true,
        'recorded' => true,
        'comparisons' => RevocationLatencyResults::getComparisons(),
        'overall' => RevocationLatencyResults::getOverall(),
    ]);
})->middleware('auth')->name('dashboard.revocation-latency.record-result');

Route::get('/dashboard/revocation-latency/test-results', function () {
    return response()->json([
        'success' => true,
        'comparisons' => RevocationLatencyResults::getComparisons(),
        'overall' => RevocationLatencyResults::getOverall(),
    ]);
})->middleware('auth')->name('dashboard.revocation-latency.test-results');

Route::post('/dashboard/attack-success-rate/test/{type}', function (Request $request, string $type) {
    if (! in_array($type, ['session', 'token'], true)) {
        return response()->json(['message' => 'Unsupported authentication type.'], 422);
    }

    $credential = trim((string) $request->input('credential', ''));

    if ($credential === '') {
        return response()->json(['message' => 'A captured credential is required.'], 422);
    }

    $isValid = false;
    $victimId = null;
    $victimName = null;
    $victimEmail = null;

    if ($type === 'session') {
        $session = DB::table('sessions')->where('id', $credential)->first();
        $isValid = (bool) ($session && $session->last_activity >= (time() - (config('session.lifetime') * 60)));

        $phishMetric = ExperimentMetric::where('action', 'link_clicked')
            ->where('victim_authentication_type', 'session')
            ->where('victim_session_id', $credential)
            ->latest('created_at')
            ->first();

        $victimId = $session?->user_id ?? $phishMetric?->victim_id;
        $victimName = $phishMetric?->victim_name;
        $victimEmail = $phishMetric?->victim_email;
    } else {
        $payload = JwtHelper::decodePayload($credential);
        $isValid = $payload !== null && ! JwtHelper::isExpired($credential) && JwtHelper::verifySignature($credential);
        $tokenUser = User::where('api_token', $credential)->first();
        $phishMetric = ExperimentMetric::where('action', 'link_clicked')
            ->where('victim_authentication_type', 'token')
            ->where('victim_token', $credential)
            ->latest('created_at')
            ->first();

        $victimId = $payload['sub'] ?? $tokenUser?->id ?? $phishMetric?->victim_id;
        $victimName = $phishMetric?->victim_name ?? $tokenUser?->name;
        $victimEmail = $phishMetric?->victim_email ?? $tokenUser?->email;
    }

    if (! $victimId) {
        return response()->json([
            'valid' => $isValid,
            'recorded' => false,
            'message' => 'The captured credential could not be linked to a user.',
        ], 422);
    }

    $metric = ExperimentMetric::where('action', 'attack_success_rate_test')
        ->where('victim_authentication_type', $type)
        ->where('victim_id', (int) $victimId)
        ->latest('created_at')
        ->first();

    $metricData = [
        'auth_type' => $type,
        'action' => 'attack_success_rate_test',
        'method' => 'POST',
        'path' => $request->path(),
        'success' => $isValid,
        'victim_id' => (int) $victimId,
        'victim_name' => $victimName,
        'victim_email' => $victimEmail,
        'victim_authentication_type' => $type,
    ];

    if ($metric) {
        $metric->update($metricData);
    } else {
        ExperimentMetric::create($metricData);
    }

    $metrics = ExperimentMetric::where('action', 'attack_success_rate_test')
        ->where('victim_authentication_type', $type)
        ->select('victim_id', 'success')
        ->get();

    $usersTested = $metrics->pluck('victim_id')->unique()->count();
    $successes = $metrics->where('success', true)->count();

    return response()->json([
        'valid' => $isValid,
        'recorded' => true,
        'victim_id' => (int) $victimId,
        'users_tested' => $usersTested,
        'successes' => $successes,
        'failed' => $metrics->count() - $successes,
        'success_rate' => $metrics->count() ? round(($successes / $metrics->count()) * 100) : 0,
    ]);
})->name('dashboard.attack-success-rate.test');

Route::post('/dashboard/attack-success-rate/reset', function () {
    ExperimentMetric::where('action', 'attack_success_rate_test')->delete();

    return response()->json(['success' => true]);
})->name('dashboard.attack-success-rate.reset');

Route::get('/dashboard/attack-success-rate/comparisons', function () {
    return response()->json([
        'success' => true,
        'comparisons' => AttackSuccessRateResults::getComparisons(),
        'overall' => AttackSuccessRateResults::getOverall(),
    ]);
})->middleware('auth')->name('dashboard.attack-success-rate.comparisons');

Route::post('/dashboard/data-exposure-risk/analyze', function (Request $request, CredentialExposureAnalyzer $analyzer) {
    $validated = $request->validate([
        'session_id' => 'nullable|string|min:5',
        'token' => 'nullable|string|min:5',
    ]);

    if (empty($validated['session_id']) && empty($validated['token'])) {
        return response()->json([
            'message' => 'Enter at least one captured credential to analyze.',
        ], 422);
    }

    $sessionResult = null;
    if (! empty($validated['session_id'])) {
        $sessionResult = $analyzer->analyzeSession($validated['session_id']);
    }

    $tokenResult = null;
    if (! empty($validated['token'])) {
        $tokenResult = $analyzer->analyzeToken($validated['token']);
    }

    return response()->json([
        'success' => true,
        'session' => $sessionResult,
        'token' => $tokenResult,
    ]);
})->name('dashboard.data-exposure-risk.analyze');

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

Route::middleware(['web','auth'])->group(function () {
    // Route::get('/dashboard', function () {
    //     return view('dashboard');
    // })->name('home');

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
                if (empty($victimUser->api_token) || JwtHelper::isExpired($victimUser->api_token)) {
                    $victimUser->createApiToken();
                }
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
