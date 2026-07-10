<?php

namespace App\Services;

use App\Models\ExperimentMetric;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricRecorder
{
    public static function log(
        string $authType,
        string $action,
        Request $request,
        int $durationMs,
        int $memoryUsage,
        int $queryCount,
        int $storageBytes = 0,
        bool $success = true
    ): void {
        try {
            ExperimentMetric::create([
                'auth_type' => $authType,
                'action' => $action,
                'method' => $request->method(),
                'path' => $request->path(),
                'duration_ms' => $durationMs,
                'memory_usage' => $memoryUsage,
                'query_count' => $queryCount,
                'storage_bytes' => $storageBytes,
                'success' => $success,
            ]);
        } catch (QueryException $e) {
            // ignore metric persistence failures for now
        }
    }

    public static function sessionStorageBytes(Request $request): int
    {
        $sessionId = $request->session()->getId();
        $payload = DB::table('sessions')->where('id', $sessionId)->value('payload');

        return $payload ? strlen($payload) : 0;
    }

    public static function tokenStorageBytes(?string $token): int
    {
        return $token ? strlen($token) : 0;
    }

    public static function start(): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        return [
            'time' => microtime(true),
            'memory' => memory_get_usage(true),
        ];
    }

    public static function finish(array $start): array
    {
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        return [
            'duration_ms' => (int) round((microtime(true) - $start['time']) * 1000),
            'memory_usage' => memory_get_usage(true) - $start['memory'],
            'query_count' => $queries,
        ];
    }
}
