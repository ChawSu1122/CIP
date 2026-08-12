<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class RevocationLatencyStore
{
    private const TTL_SECONDS = 86400;

    public static function recordSessionLogout(string $sessionId, string $logoutTime): void
    {
        if ($sessionId === '') {
            return;
        }

        Cache::put(self::sessionKey($sessionId), $logoutTime, self::TTL_SECONDS);
    }

    public static function recordTokenLogout(string $token, string $logoutTime): void
    {
        if ($token === '') {
            return;
        }

        Cache::put(self::tokenKey($token), $logoutTime, self::TTL_SECONDS);
    }

    public static function getSessionLogoutTime(string $sessionId): ?string
    {
        if ($sessionId === '') {
            return null;
        }

        $value = Cache::get(self::sessionKey($sessionId));

        return is_string($value) && $value !== '' ? $value : null;
    }

    public static function getTokenLogoutTime(string $token): ?string
    {
        if ($token === '') {
            return null;
        }

        $value = Cache::get(self::tokenKey($token));

        return is_string($value) && $value !== '' ? $value : null;
    }

    private static function sessionKey(string $sessionId): string
    {
        return 'revocation_logout:session:'.$sessionId;
    }

    private static function tokenKey(string $token): string
    {
        return 'revocation_logout:token:'.hash('sha256', $token);
    }
}
