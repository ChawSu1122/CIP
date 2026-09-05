<?php

namespace App\Support;

use App\Models\ExperimentMetric;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RevocationLatencyResults
{
    public const ACTION = 'revocation_latency_test';

    public static function record(string $type, string $credential, int $latencySeconds, ?string $comparisonId = null): ?ExperimentMetric
    {
        if (! in_array($type, ['session', 'token'], true)) {
            return null;
        }

        $victimId = null;
        $victimName = null;
        $victimEmail = null;

        if ($type === 'session') {
            $session = DB::table('sessions')->where('id', $credential)->first();

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
            return null;
        }

        $attributes = [
            'auth_type' => $type,
            'method' => 'POST',
            'duration_ms' => $latencySeconds * 1000,
            'victim_id' => (int) $victimId,
            'victim_name' => $victimName,
            'victim_email' => $victimEmail,
        ];

        if ($comparisonId !== null && $comparisonId !== '') {
            return ExperimentMetric::updateOrCreate(
                [
                    'action' => self::ACTION,
                    'victim_authentication_type' => $type,
                    'comparison_id' => $comparisonId,
                ],
                $attributes
            );
        }

        return ExperimentMetric::updateOrCreate(
            [
                'action' => self::ACTION,
                'victim_authentication_type' => $type,
                'victim_id' => (int) $victimId,
            ],
            $attributes
        );
    }

    public static function getComparisons(): array
    {
        $rows = ExperimentMetric::where('action', self::ACTION)
            ->orderBy('created_at')
            ->get();

        $groups = [];
        $legacySession = [];
        $legacyToken = [];

        foreach ($rows as $row) {
            $comparisonId = $row->comparison_id;

            if ($comparisonId !== null && $comparisonId !== '') {
                if (! isset($groups[$comparisonId])) {
                    $groups[$comparisonId] = [
                        'comparison_id' => $comparisonId,
                        'victim_id' => $row->victim_id,
                        'session_victim_name' => null,
                        'token_victim_name' => null,
                        'order' => $row->created_at,
                        'session_rl_seconds' => null,
                        'token_rl_seconds' => null,
                    ];
                }

                if ($row->victim_authentication_type === 'session') {
                    $groups[$comparisonId]['session_rl_seconds'] = $row->duration_ms / 1000;

                    if ($row->victim_name) {
                        $groups[$comparisonId]['session_victim_name'] = $row->victim_name;
                    }
                } elseif ($row->victim_authentication_type === 'token') {
                    $groups[$comparisonId]['token_rl_seconds'] = $row->duration_ms / 1000;

                    if ($row->victim_name) {
                        $groups[$comparisonId]['token_victim_name'] = $row->victim_name;
                    }
                }

                continue;
            }

            if ($row->victim_authentication_type === 'session') {
                $legacySession[] = $row;
            } elseif ($row->victim_authentication_type === 'token') {
                $legacyToken[] = $row;
            }
        }

        $roundCount = max(count($legacySession), count($legacyToken));

        for ($i = 0; $i < $roundCount; $i++) {
            $session = $legacySession[$i] ?? null;
            $token = $legacyToken[$i] ?? null;
            $key = 'legacy-round:' . ($i + 1);

            $groups[$key] = [
                'comparison_id' => $key,
                'victim_id' => $session?->victim_id ?? $token?->victim_id,
                'session_victim_name' => $session?->victim_name,
                'token_victim_name' => $token?->victim_name,
                'order' => $session
                    ? ($token
                        ? ($session->created_at->lt($token->created_at) ? $session->created_at : $token->created_at)
                        : $session->created_at)
                    : $token->created_at,
                'session_rl_seconds' => $session ? $session->duration_ms / 1000 : null,
                'token_rl_seconds' => $token ? $token->duration_ms / 1000 : null,
            ];
        }

        $comparisons = array_values($groups);

        usort($comparisons, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        return array_map(function (array $comparison): array {
            return [
                'comparison_id' => $comparison['comparison_id'],
                'victim_id' => $comparison['victim_id'],
                'session_victim_name' => $comparison['session_victim_name'],
                'token_victim_name' => $comparison['token_victim_name'],
                'session_rl_minutes' => $comparison['session_rl_seconds'] !== null
                    ? round($comparison['session_rl_seconds'] / 60, 2)
                    : null,
                'token_rl_minutes' => $comparison['token_rl_seconds'] !== null
                    ? round($comparison['token_rl_seconds'] / 60, 2)
                    : null,
                'session_rl_seconds' => $comparison['session_rl_seconds'] !== null
                    ? (int) round($comparison['session_rl_seconds'])
                    : null,
                'token_rl_seconds' => $comparison['token_rl_seconds'] !== null
                    ? (int) round($comparison['token_rl_seconds'])
                    : null,
            ];
        }, $comparisons);
    }

    public static function getOverall(): ?array
    {
        $metrics = ExperimentMetric::where('action', self::ACTION)->get();

        if ($metrics->isEmpty()) {
            return null;
        }

        $sessionRows = $metrics->where('victim_authentication_type', 'session');
        $tokenRows = $metrics->where('victim_authentication_type', 'token');

        $sessionSeconds = $sessionRows->map(fn ($row) => $row->duration_ms / 1000);
        $tokenSeconds = $tokenRows->map(fn ($row) => $row->duration_ms / 1000);

        return [
            'session_rl_minutes' => $sessionSeconds->isEmpty()
                ? null
                : round($sessionSeconds->avg() / 60, 2),
            'token_rl_minutes' => $tokenSeconds->isEmpty()
                ? null
                : round($tokenSeconds->avg() / 60, 2),
            'session_rl_seconds' => $sessionSeconds->isEmpty()
                ? null
                : (int) round($sessionSeconds->avg()),
            'token_rl_seconds' => $tokenSeconds->isEmpty()
                ? null
                : (int) round($tokenSeconds->avg()),
            'session_count' => $sessionRows->count(),
            'token_count' => $tokenRows->count(),
        ];
    }
}