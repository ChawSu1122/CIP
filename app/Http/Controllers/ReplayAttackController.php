<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MetricRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReplayAttackController extends Controller
{
    public function demo(): View
    {
        return view('thesis.replay-attack');
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
