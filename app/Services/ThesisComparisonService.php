<?php

namespace App\Services;

use App\Models\ExperimentMetric;
use Illuminate\Support\Facades\DB;

class ThesisComparisonService
{
    public function getComparisonData(): array
    {
        $loginMetrics = ExperimentMetric::where('action', 'login')->get();
        $sessionLogins = $loginMetrics->where('auth_type', 'session');
        $tokenLogins = $loginMetrics->where('auth_type', 'token');

        $sessionActions = ExperimentMetric::where('auth_type', 'session')
            ->whereNotIn('action', ['login', 'login_failed', 'replay_attempt'])
            ->get();

        $tokenActions = ExperimentMetric::where('auth_type', 'token')
            ->whereNotIn('action', ['login', 'login_failed', 'replay_attempt'])
            ->get();

        $sessionFailed = ExperimentMetric::where('auth_type', 'session')
            ->where('action', 'login_failed')
            ->count();

        $tokenFailed = ExperimentMetric::where('auth_type', 'token')
            ->where('action', 'login_failed')
            ->count();

        $sessionReplay = ExperimentMetric::where('auth_type', 'session')
            ->where('action', 'replay_attempt')
            ->get();

        $tokenReplay = ExperimentMetric::where('auth_type', 'token')
            ->where('action', 'replay_attempt')
            ->get();

        $activeSessions = DB::table('sessions')->count();
        $activeTokens = DB::table('users')->whereNotNull('api_token')->count();

        $sessionStorageTotal = (int) DB::table('sessions')
            ->selectRaw('COALESCE(SUM(LENGTH(payload)), 0) as total')
            ->value('total');

        $tokenStorageTotal = (int) DB::table('users')
            ->whereNotNull('api_token')
            ->selectRaw('COALESCE(SUM(LENGTH(api_token)), 0) as total')
            ->value('total');

        $scalability = $this->buildScalabilityComparison($sessionLogins, $tokenLogins, $sessionActions, $tokenActions);
        $storage = $this->buildStorageComparison(
            $sessionLogins,
            $tokenLogins,
            $activeSessions,
            $activeTokens,
            $sessionStorageTotal,
            $tokenStorageTotal
        );
        $security = $this->buildSecurityComparison(
            $sessionLogins,
            $tokenLogins,
            $sessionFailed,
            $tokenFailed,
            $sessionReplay,
            $tokenReplay
        );

        return [
            'loginMetrics' => $this->formatLoginMetrics($sessionLogins, $tokenLogins),
            'actionMetrics' => $this->formatActionMetrics($sessionActions, $tokenActions),
            'scalability' => $scalability,
            'storage' => $storage,
            'security' => $security,
            'totals' => [
                'session_logins' => $sessionLogins->count(),
                'token_logins' => $tokenLogins->count(),
                'session_failed' => $sessionFailed,
                'token_failed' => $tokenFailed,
                'session_replay_success' => $sessionReplay->where('success', true)->count(),
                'session_replay_blocked' => $sessionReplay->where('success', false)->count(),
                'token_replay_success' => $tokenReplay->where('success', true)->count(),
                'token_replay_blocked' => $tokenReplay->where('success', false)->count(),
                'active_sessions' => $activeSessions,
                'active_tokens' => $activeTokens,
            ],
        ];
    }

    private function buildScalabilityComparison($sessionLogins, $tokenLogins, $sessionActions, $tokenActions): array
    {
        $session = [
            'avg_duration_ms' => round($sessionLogins->avg('duration_ms') ?? 0, 1),
            'avg_memory_bytes' => round($sessionLogins->avg('memory_usage') ?? 0, 1),
            'avg_queries' => round($sessionLogins->avg('query_count') ?? 0, 1),
            'action_avg_duration_ms' => round($sessionActions->avg('duration_ms') ?? 0, 1),
            'login_count' => $sessionLogins->count(),
            'total_requests' => $sessionLogins->count() + $sessionActions->count(),
        ];

        $token = [
            'avg_duration_ms' => round($tokenLogins->avg('duration_ms') ?? 0, 1),
            'avg_memory_bytes' => round($tokenLogins->avg('memory_usage') ?? 0, 1),
            'avg_queries' => round($tokenLogins->avg('query_count') ?? 0, 1),
            'action_avg_duration_ms' => round($tokenActions->avg('duration_ms') ?? 0, 1),
            'login_count' => $tokenLogins->count(),
            'total_requests' => $tokenLogins->count() + $tokenActions->count(),
        ];

        $sessionScore = $this->scalabilityScore($session);
        $tokenScore = $this->scalabilityScore($token);

        $winner = $this->pickWinner($sessionScore, $tokenScore, 'token');

        return [
            'session' => array_merge($session, ['score' => $sessionScore]),
            'token' => array_merge($token, ['score' => $tokenScore]),
            'winner' => $winner,
            'verdict' => $this->scalabilityVerdict($winner),
        ];
    }

    private function buildStorageComparison(
        $sessionLogins,
        $tokenLogins,
        int $activeSessions,
        int $activeTokens,
        int $sessionStorageTotal,
        int $tokenStorageTotal
    ): array {
        $session = [
            'avg_bytes_per_login' => round($sessionLogins->avg('storage_bytes') ?? 0, 1),
            'total_bytes' => $sessionStorageTotal,
            'active_records' => $activeSessions,
            'login_count' => $sessionLogins->count(),
        ];

        $token = [
            'avg_bytes_per_login' => round($tokenLogins->avg('storage_bytes') ?? 0, 1),
            'total_bytes' => $tokenStorageTotal,
            'active_records' => $activeTokens,
            'login_count' => $tokenLogins->count(),
        ];

        $sessionScore = $this->storageScore($session);
        $tokenScore = $this->storageScore($token);

        $winner = $this->pickWinner($sessionScore, $tokenScore, 'token');

        return [
            'session' => array_merge($session, ['score' => $sessionScore]),
            'token' => array_merge($token, ['score' => $tokenScore]),
            'winner' => $winner,
            'verdict' => $this->storageVerdict($winner),
        ];
    }

    private function buildSecurityComparison(
        $sessionLogins,
        $tokenLogins,
        int $sessionFailed,
        int $tokenFailed,
        $sessionReplay,
        $tokenReplay
    ): array {
        $sessionSuccess = $sessionLogins->count();
        $tokenSuccess = $tokenLogins->count();

        $sessionTotal = $sessionSuccess + $sessionFailed;
        $tokenTotal = $tokenSuccess + $tokenFailed;

        $sessionReplaySuccess = $sessionReplay->where('success', true)->count();
        $sessionReplayBlocked = $sessionReplay->where('success', false)->count();
        $tokenReplaySuccess = $tokenReplay->where('success', true)->count();
        $tokenReplayBlocked = $tokenReplay->where('success', false)->count();

        $sessionReplayTotal = $sessionReplaySuccess + $sessionReplayBlocked;
        $tokenReplayTotal = $tokenReplaySuccess + $tokenReplayBlocked;

        $session = [
            'successful_logins' => $sessionSuccess,
            'failed_logins' => $sessionFailed,
            'success_rate' => $sessionTotal > 0 ? round(($sessionSuccess / $sessionTotal) * 100, 1) : 100,
            'replay_attempts' => $sessionReplayTotal,
            'replay_success' => $sessionReplaySuccess,
            'replay_blocked' => $sessionReplayBlocked,
            'replay_vulnerability_rate' => $sessionReplayTotal > 0
                ? round(($sessionReplaySuccess / $sessionReplayTotal) * 100, 1)
                : 0,
            'csrf_protected' => true,
            'http_only_cookie' => true,
            'bearer_token_exposure' => false,
            'server_side_revocation' => true,
        ];

        $token = [
            'successful_logins' => $tokenSuccess,
            'failed_logins' => $tokenFailed,
            'success_rate' => $tokenTotal > 0 ? round(($tokenSuccess / $tokenTotal) * 100, 1) : 100,
            'replay_attempts' => $tokenReplayTotal,
            'replay_success' => $tokenReplaySuccess,
            'replay_blocked' => $tokenReplayBlocked,
            'replay_vulnerability_rate' => $tokenReplayTotal > 0
                ? round(($tokenReplaySuccess / $tokenReplayTotal) * 100, 1)
                : 0,
            'csrf_protected' => false,
            'http_only_cookie' => false,
            'bearer_token_exposure' => true,
            'server_side_revocation' => true,
        ];

        $sessionScore = $this->securityScore($session);
        $tokenScore = $this->securityScore($token);

        $winner = $this->pickWinner($sessionScore, $tokenScore, 'session');

        $replayWinner = $session['replay_vulnerability_rate'] <= $token['replay_vulnerability_rate'] ? 'session' : 'token';

        return [
            'session' => array_merge($session, ['score' => $sessionScore]),
            'token' => array_merge($token, ['score' => $tokenScore]),
            'winner' => $winner,
            'replay_winner' => $replayWinner,
            'verdict' => $this->securityVerdict($winner, $session, $token),
        ];
    }

    private function scalabilityScore(array $metrics): int
    {
        if ($metrics['login_count'] === 0 && $metrics['total_requests'] === 0) {
            return 50;
        }

        $durationScore = max(0, 100 - ($metrics['avg_duration_ms'] / 2));
        $queryScore = max(0, 100 - ($metrics['avg_queries'] * 15));
        $memoryScore = max(0, 100 - ($metrics['avg_memory_bytes'] / 1024));

        return (int) round(($durationScore + $queryScore + $memoryScore) / 3);
    }

    private function storageScore(array $metrics): int
    {
        $bytes = max(1, (int) ($metrics['avg_bytes_per_login'] ?: $metrics['total_bytes']));
        $total = max(1, (int) $metrics['total_bytes']);

        $perLoginScore = max(0, 100 - ($bytes / 10));
        $totalScore = max(0, 100 - ($total / 100));

        return (int) round(($perLoginScore + $totalScore) / 2);
    }

    private function securityScore(array $metrics): int
    {
        $score = (int) ($metrics['success_rate'] ?? 100);

        if ($metrics['csrf_protected']) {
            $score += 10;
        }
        if ($metrics['http_only_cookie']) {
            $score += 10;
        }
        if ($metrics['server_side_revocation']) {
            $score += 5;
        }
        if ($metrics['bearer_token_exposure']) {
            $score -= 15;
        }

        $replayRate = (int) ($metrics['replay_vulnerability_rate'] ?? 0);
        $score -= (int) round($replayRate / 5);

        if (($metrics['replay_blocked'] ?? 0) > 0) {
            $score += 5;
        }

        return min(100, max(0, $score));
    }

    private function pickWinner(int $sessionScore, int $tokenScore, string $tieDefault): string
    {
        if ($sessionScore === $tokenScore) {
            return $tieDefault;
        }

        return $sessionScore > $tokenScore ? 'session' : 'token';
    }

    private function scalabilityVerdict(string $winner): string
    {
        if ($winner === 'token') {
            return 'Token-based authentication scales better because each request is self-contained, reducing server-side session lookups and shared storage requirements across multiple servers.';
        }

        return 'Session-based authentication can scale well with proper infrastructure (Redis, load balancer sticky sessions), but requires more coordination than token-based approaches.';
    }

    private function storageVerdict(string $winner): string
    {
        if ($winner === 'token') {
            return 'Token-based authentication uses less server storage because credentials travel with the client instead of persisting full session payloads on the server.';
        }

        return 'Session-based authentication stores user state on the server, which increases storage needs as more users remain logged in simultaneously.';
    }

    private function securityVerdict(string $winner, array $session, array $token): string
    {
        $replayNote = '';
        if ($session['replay_attempts'] > 0 || $token['replay_attempts'] > 0) {
            $safer = $session['replay_vulnerability_rate'] <= $token['replay_vulnerability_rate'] ? 'Session' : 'Token';
            $replayNote = ' Replay attack demo: ' . $safer . ' auth had fewer successful replays. ';
        }

        if ($winner === 'session') {
            return $replayNote . 'Session-based authentication is generally stronger for browser apps because HttpOnly cookies and CSRF protection reduce common web attack vectors.';
        }

        return $replayNote . 'Token-based authentication avoids CSRF but requires careful token storage on the client to prevent leakage and replay attacks.';
    }

    private function formatLoginMetrics($sessionLogins, $tokenLogins): array
    {
        return [
            $this->metricRow('session', 'login', $sessionLogins),
            $this->metricRow('token', 'login', $tokenLogins),
        ];
    }

    private function formatActionMetrics($sessionActions, $tokenActions): array
    {
        $actions = $sessionActions->pluck('action')
            ->merge($tokenActions->pluck('action'))
            ->unique()
            ->values();

        $rows = [];
        foreach ($actions as $action) {
            $rows[] = $this->metricRow('session', $action, $sessionActions->where('action', $action));
            $rows[] = $this->metricRow('token', $action, $tokenActions->where('action', $action));
        }

        return $rows;
    }

    private function metricRow(string $authType, string $action, $collection): array
    {
        return [
            'auth_type' => $authType,
            'action' => $action,
            'avg_duration' => round($collection->avg('duration_ms') ?? 0, 1),
            'avg_memory' => round($collection->avg('memory_usage') ?? 0, 1),
            'avg_queries' => round($collection->avg('query_count') ?? 0, 1),
            'avg_storage_bytes' => round($collection->avg('storage_bytes') ?? 0, 1),
            'runs' => $collection->count(),
        ];
    }
}
