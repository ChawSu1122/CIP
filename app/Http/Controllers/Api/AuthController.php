<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\MetricRecorder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $start = MetricRecorder::start();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            $result = MetricRecorder::finish($start);
            MetricRecorder::log(
                'token',
                'login_failed',
                $request,
                $result['duration_ms'],
                $result['memory_usage'],
                $result['query_count'],
                0,
                false
            );

            return response()->json(['message' => 'Incorrect email or password. Please try again.'], 422);
        }

        $token = $user->createApiToken();
        $result = MetricRecorder::finish($start);
        MetricRecorder::log(
            'token',
            'login',
            $request,
            $result['duration_ms'],
            $result['memory_usage'],
            $result['query_count'],
            MetricRecorder::tokenStorageBytes($token),
            true
        );

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->only(['id', 'name', 'email']),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $this->authenticate($request);

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        $user->revokeApiToken();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->authenticate($request);

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        return response()->json($user->only(['id', 'name', 'email']));
    }
}
