<?php

namespace App\Support;

use App\Models\ExperimentMetric;
use Illuminate\Support\Collection;

class AttackSuccessRateResults
{
    public const ACTION = 'attack_success_rate_test';

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
                        'session_success' => null,
                        'token_success' => null,
                    ];
                }

                if ($row->victim_authentication_type === 'session') {
                    $groups[$comparisonId]['session_success'] = (bool) $row->success;

                    if ($row->victim_name) {
                        $groups[$comparisonId]['session_victim_name'] = $row->victim_name;
                    }
                } elseif ($row->victim_authentication_type === 'token') {
                    $groups[$comparisonId]['token_success'] = (bool) $row->success;

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
                'session_success' => $session ? (bool) $session->success : null,
                'token_success' => $token ? (bool) $token->success : null,
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
                'session_rate' => $comparison['session_success'] !== null
                    ? (int) ($comparison['session_success'] ? 100 : 0)
                    : null,
                'token_rate' => $comparison['token_success'] !== null
                    ? (int) ($comparison['token_success'] ? 100 : 0)
                    : null,
            ];
        }, $comparisons);
    }

    public static function getOverall(): ?array
    {
        $comparisons = collect(self::getComparisons());

        if ($comparisons->isEmpty()) {
            return null;
        }

        $sessionRates = self::collectRates($comparisons, 'session_rate');
        $tokenRates = self::collectRates($comparisons, 'token_rate');

        return [
            'session_rate' => $sessionRates->isEmpty()
                ? null
                : (int) round($sessionRates->average()),
            'token_rate' => $tokenRates->isEmpty()
                ? null
                : (int) round($tokenRates->average()),
            'session_count' => $sessionRates->count(),
            'token_count' => $tokenRates->count(),
        ];
    }

    private static function collectRates(Collection $comparisons, string $field): Collection
    {
        return $comparisons
            ->pluck($field)
            ->filter(function ($rate) {
                return $rate !== null;
            })
            ->values();
    }
}