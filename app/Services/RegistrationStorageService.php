<?php

namespace App\Services;

use App\Models\RegistrationStorageMetric;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationStorageService
{
    /**
     * Empirical Storage Footprint Measurement:
     * total_bytes = user_row_bytes + session_row_bytes + token_bytes
     * total_kb    = total_bytes / 1024
     */
    public function recordSessionRegistration(User $user, Request $request): RegistrationStorageMetric
    {
        $request->session()->save();
        $sessionId = $request->session()->getId();

        $userRowBytes = $this->measureUserRowBytes($user->id);
        $sessionRowBytes = $this->measureSessionRowBytes($sessionId);
        $totalBytes = $userRowBytes + $sessionRowBytes;

        return RegistrationStorageMetric::create([
            'user_id' => $user->id,
            'auth_method' => 'session',
            'user_name' => $user->name,
            'user_row_bytes' => $userRowBytes,
            'session_row_bytes' => $sessionRowBytes,
            'token_bytes' => 0,
            'total_bytes' => $totalBytes,
            'total_kb' => round($totalBytes / 1024, 4),
        ]);
    }

    public function recordTokenRegistration(User $user): RegistrationStorageMetric
    {
        $user->refresh();

        $userRowBytes = $this->measureUserRowBytes($user->id);
        $tokenBytes = strlen($user->api_token ?? '');
        $totalBytes = $userRowBytes + $tokenBytes;

        return RegistrationStorageMetric::create([
            'user_id' => $user->id,
            'auth_method' => 'token',
            'user_name' => $user->name,
            'user_row_bytes' => $userRowBytes,
            'session_row_bytes' => 0,
            'token_bytes' => $tokenBytes,
            'total_bytes' => $totalBytes,
            'total_kb' => round($totalBytes / 1024, 4),
        ]);
    }

    public function getRegistrationComparison(): array
    {
        $sessionMetrics = RegistrationStorageMetric::where('auth_method', 'session')->get();
        $tokenMetrics = RegistrationStorageMetric::where('auth_method', 'token')->get();

        $session = [
            'register_count' => $sessionMetrics->count(),
            'avg_kb' => round($sessionMetrics->avg('total_kb') ?? 0, 4),
            'total_kb' => round($sessionMetrics->sum('total_kb'), 4),
            'min_kb' => round($sessionMetrics->min('total_kb') ?? 0, 4),
            'max_kb' => round($sessionMetrics->max('total_kb') ?? 0, 4),
            'avg_user_row_kb' => round(($sessionMetrics->avg('user_row_bytes') ?? 0) / 1024, 4),
            'avg_session_row_kb' => round(($sessionMetrics->avg('session_row_bytes') ?? 0) / 1024, 4),
        ];

        $token = [
            'register_count' => $tokenMetrics->count(),
            'avg_kb' => round($tokenMetrics->avg('total_kb') ?? 0, 4),
            'total_kb' => round($tokenMetrics->sum('total_kb'), 4),
            'min_kb' => round($tokenMetrics->min('total_kb') ?? 0, 4),
            'max_kb' => round($tokenMetrics->max('total_kb') ?? 0, 4),
            'avg_user_row_kb' => round(($tokenMetrics->avg('user_row_bytes') ?? 0) / 1024, 4),
            'avg_token_kb' => round(($tokenMetrics->avg('token_bytes') ?? 0) / 1024, 4),
        ];

        $winner = 'token';
        if ($session['register_count'] > 0 && $token['register_count'] > 0) {
            $winner = $session['avg_kb'] <= $token['avg_kb'] ? 'session' : 'token';
        } elseif ($session['register_count'] > 0) {
            $winner = 'session';
        }

        $percentDiff = 0;
        if ($session['avg_kb'] > 0 && $token['avg_kb'] > 0) {
            $higher = max($session['avg_kb'], $token['avg_kb']);
            $lower = min($session['avg_kb'], $token['avg_kb']);
            $percentDiff = round((($higher - $lower) / $higher) * 100, 1);
        }

        return [
            'session' => $session,
            'token' => $token,
            'winner' => $winner,
            'percent_difference' => $percentDiff,
            'verdict' => $this->verdict($winner),
            'methodology' => 'Empirical Storage Footprint Measurement — byte size of database records created during registration, converted to KB (bytes ÷ 1024).',
            'recent' => RegistrationStorageMetric::latest()->take(10)->get()->map(fn ($m) => [
                'user_name' => $m->user_name,
                'auth_method' => $m->auth_method,
                'total_kb' => $m->total_kb,
                'user_row_kb' => round($m->user_row_bytes / 1024, 4),
                'session_row_kb' => round($m->session_row_bytes / 1024, 4),
                'token_kb' => round($m->token_bytes / 1024, 4),
                'created_at' => $m->created_at?->toDateTimeString(),
            ]),
        ];
    }

    private function measureUserRowBytes(int $userId): int
    {
        return (int) DB::table('users')
            ->where('id', $userId)
            ->selectRaw('
                COALESCE(LENGTH(name), 0)
                + COALESCE(LENGTH(email), 0)
                + COALESCE(LENGTH(password), 0)
                + COALESCE(LENGTH(remember_token), 0)
                + COALESCE(LENGTH(auth_method), 0)
                + 8 + 8
                as bytes
            ')
            ->value('bytes');
    }

    private function measureSessionRowBytes(string $sessionId): int
    {
        return (int) DB::table('sessions')
            ->where('id', $sessionId)
            ->selectRaw('
                COALESCE(LENGTH(id), 0)
                + COALESCE(LENGTH(ip_address), 0)
                + COALESCE(LENGTH(user_agent), 0)
                + COALESCE(LENGTH(payload), 0)
                + 8 + 4
                as bytes
            ')
            ->value('bytes') ?? 0;
    }

    private function verdict(string $winner): string
    {
        if ($winner === 'token') {
            return 'Token-based registration wins on storage because it stores only the user record and a small API token, without creating a session payload row.';
        }

        return 'Session-based registration uses more storage because it creates both a user record and a session payload in the sessions table.';
    }
}
