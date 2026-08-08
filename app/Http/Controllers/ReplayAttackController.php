<?php

namespace App\Http\Controllers;

use App\Models\ExperimentMetric;
use App\Models\User;
use App\Services\MetricRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReplayAttackController extends Controller
{
    public function demo(Request $request): View
    {
        $victimId = $request->query('victim_id');
        $victimName = $request->query('victim_name', 'Alice');
        $victimEmail = $request->query('victim_email', 'alice@gmail.com');
        $attackerId = $request->query('attacker_id');

        if ($request->hasAny(['victim_id', 'victim_name', 'victim_email', 'attacker_id'])) {
            $authType = $request->query('victim_authentication_type', $request->session()->get('victim_authentication_type', 'session'));
            $capturedSessionId = null;
            $capturedToken = null;
            $victimUser = null;

            if ($victimId) {
                $victimUser = User::find($victimId);
            }

            if ($authType === 'session') {
                $capturedSessionId = $request->query('victim_session_id', $request->session()->get('victim_session_id'));
            }

            if ($authType === 'token' && $victimUser) {
                $capturedToken = $victimUser->api_token;
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
                'victim_id' => $victimId,
                'victim_name' => $victimName,
                'victim_email' => $victimEmail,
                'victim_authentication_type' => $authType,
                'victim_session_id' => $capturedSessionId,
                'victim_token' => $capturedToken,
                'attacker_id' => $attackerId,
            ]);
        }

        $latestVictim = ExperimentMetric::where('auth_type', 'phish')
            ->where('action', 'link_clicked')
            ->latest()
            ->first();

        $victimAuthType = $latestVictim?->victim_authentication_type ?? 'session';
        $showTokenReplay = $victimAuthType === 'token';

        return view('thesis.replay-attack', [
            'victimName' => $latestVictim?->victim_name ?? $victimName,
            'victimEmail' => $latestVictim?->victim_email ?? $victimEmail,
            'victimId' => $latestVictim?->victim_id ?? $victimId,
            'attackerId' => $latestVictim?->attacker_id ?? $attackerId,
            'capturedSessionId' => $latestVictim?->victim_session_id ?? null,
            'capturedToken' => $latestVictim?->victim_token ?? null,
            'showTokenReplay' => $showTokenReplay,
        ]);
    }

    public function compromise(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'victim_id' => 'required|exists:users,id',
            'victim_authentication_type' => 'required|in:session,token',
            'compromised_session_id' => 'nullable|string',
            'compromised_token' => 'nullable|string',
        ]);

        $victim = User::findOrFail($validated['victim_id']);
        $metric = ExperimentMetric::where('victim_id', $victim->id)
            ->where('auth_type', 'phish')
            ->where('action', 'link_clicked')
            ->latest()
            ->first();

        if (! $metric) {
            return response()->json([
                'success' => false,
                'message' => 'No phishing event record was found for this victim.',
            ], 404);
        }

        $expectedType = $metric->victim_authentication_type ?? 'session';
        $currentUserAgent = $request->header('User-Agent', '');
        $storedVictimUserAgent = $metric->victim_user_agent;
        $differentBrowser = ! empty($storedVictimUserAgent)
            && ! empty($currentUserAgent)
            && strcasecmp($storedVictimUserAgent, $currentUserAgent) !== 0;

        $credentialMatches = false;

        if ($expectedType === 'session') {
            $credentialMatches = ! empty($validated['compromised_session_id'])
                && $validated['compromised_session_id'] === $metric->victim_session_id;
        } else {
            $credentialMatches = ! empty($validated['compromised_token'])
                && $validated['compromised_token'] === $metric->victim_token;
        }

        $success = $differentBrowser && $credentialMatches;

        ExperimentMetric::create([
            'auth_type' => 'phish',
            'action' => 'compromise_detected',
            'method' => 'POST',
            'path' => 'thesis/replay/compromise',
            'duration_ms' => 0,
            'memory_usage' => 0,
            'query_count' => 0,
            'storage_bytes' => 0,
            'success' => $success,
            'victim_id' => $victim->id,
            'victim_name' => $victim->name,
            'victim_email' => $victim->email,
            'victim_authentication_type' => $expectedType,
            'victim_session_id' => $metric->victim_session_id,
            'victim_token' => $metric->victim_token,
            'attacker_id' => Auth::id(),
            'victim_user_agent' => $storedVictimUserAgent,
            'attacker_user_agent' => $currentUserAgent,
        ]);

        if (! $success) {
            return response()->json([
                'success' => false,
                'auto_login' => false,
                'message' => 'Unauthorized access blocked.',
            ], 403);
        }

        Auth::loginUsingId($victim->id, true);
        $request->session()->regenerate();
        $request->session()->put('compromised_victim_id', $victim->id);

        return response()->json([
            'success' => true,
            'auto_login' => true,
            'message' => 'Unauthorized access succeeded.',
            'victim' => $victim->only(['id', 'name', 'email']),
        ]);
    }

    public function testTokenReplay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|min:10',
        ]);

        $start = MetricRecorder::start();
        $user = User::where('api_token', $validated['token'])->first();
        $result = MetricRecorder::finish($start);

        MetricRecorder::log(
            'token',
            'replay_attempt',
            $request,
            $result['duration_ms'],
            $result['memory_usage'],
            $result['query_count'],
            strlen($validated['token']),
            (bool) $user
        );

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Replay failed — token is invalid or has been revoked.',
                'attack_type' => 'replay',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Replay succeeded — attacker gained access using a captured bearer token without entering a password.',
            'attack_type' => 'replay',
            'impersonated_user' => $user->only(['id', 'name', 'email']),
        ]);
    }

    public function testSessionReplay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|string|min:10',
        ]);

        $start = MetricRecorder::start();
        $session = DB::table('sessions')->where('id', $validated['session_id'])->first();
        $result = MetricRecorder::finish($start);

        $isValid = $session && $session->last_activity >= (time() - (config('session.lifetime') * 60));

        MetricRecorder::log(
            'session',
            'replay_attempt',
            $request,
            $result['duration_ms'],
            $result['memory_usage'],
            $result['query_count'],
            $session ? strlen($session->payload) : 0,
            $isValid
        );

        if (! $isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Replay failed — session ID is invalid or has expired.',
                'attack_type' => 'replay',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Replay succeeded — attacker reused a captured session cookie to access the account without logging in again.',
            'attack_type' => 'replay',
            'session_info' => [
                'user_id' => $session->user_id,
                'last_activity' => date('Y-m-d H:i:s', $session->last_activity),
                'payload_bytes' => strlen($session->payload),
            ],
        ]);
    }

    public function mySessionInfo(Request $request): JsonResponse
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Not logged in via session.'], 401);
        }

        return response()->json([
            'session_id' => $request->session()->getId(),
            'user' => $request->user()->only(['id', 'name', 'email']),
            'note' => 'Copy this session ID to simulate an attacker replaying a stolen cookie.',
        ]);
    }
}
