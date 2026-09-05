<?php

namespace App\Support;

use App\Models\ExperimentMetric;
use Illuminate\Support\Collection;

class DataExposureRiskResults
{
    public const ACTION = 'data_exposure_risk_test';

    public static function record(
        string $type,
        string $credential,
        int $count,
        ?int $victimId = null,
        ?string $victimName = null,
        ?string $victimEmail = null,
        bool $found = false
    ): ?ExperimentMetric {
        if (! in_array($type, ['session', 'token'], true)) {
            return null;
        }

        $attributes = [
            'auth_type' => $type,
            'method' => 'POST',
            'duration_ms' => max(0, $count),
            'success' => $found,
            'victim_id' => $victimId,
            'victim_name' => $victimName,
            'victim_email' => $victimEmail,
        ];

        if ($type === 'session') {
            return ExperimentMetric::updateOrCreate(
                [
                    'action' => self::ACTION,
                    'victim_authentication_type' => 'session',
                    'victim_session_id' => $credential,
                ],
                $attributes
            );
        }

        return ExperimentMetric::updateOrCreate(
            [
                'action' => self::ACTION,
                'victim_authentication_type' => 'token',
                'victim_token' => $credential,
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
                        'session_count' => null,
                        'token_count' => null,
                    ];
                }

                if ($row->victim_authentication_type === 'session') {
                    $groups[$comparisonId]['session_count'] = (int) $row->duration_ms;

                    if ($row->victim_name) {
                        $groups[$comparisonId]['session_victim_name'] = $row->victim_name;
                    }
                } elseif ($row->victim_authentication_type === 'token') {
                    $groups[$comparisonId]['token_count'] = (int) $row->duration_ms;

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
                'session_count' => $session ? (int) $session->duration_ms : null,
                'token_count' => $token ? (int) $token->duration_ms : null,
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
                'session_count' => $comparison['session_count'],
                'token_count' => $comparison['token_count'],
            ];
        }, $comparisons);
    }

    public static function getOverall(): ?array
    {
        $comparisons = collect(self::getComparisons());

        if ($comparisons->isEmpty()) {
            return null;
        }

        $sessionCounts = self::collectCounts($comparisons, 'session_count');
        $tokenCounts = self::collectCounts($comparisons, 'token_count');

        return [
            'session_average' => $sessionCounts->isEmpty()
                ? null
                : round($sessionCounts->average(), 2),
            'token_average' => $tokenCounts->isEmpty()
                ? null
                : round($tokenCounts->average(), 2),
            'session_count' => $sessionCounts->count(),
            'token_count' => $tokenCounts->count(),
        ];
    }

    private static function collectCounts(Collection $comparisons, string $field): Collection
    {
        return $comparisons
            ->pluck($field)
            ->filter(function ($value) {
                return $value !== null;
            })
            ->map(function ($value) {
                return (float) $value;
            })
            ->values();
    }
}