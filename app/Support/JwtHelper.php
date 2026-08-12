<?php

namespace App\Support;

class JwtHelper
{
    public const DEMO_TOKEN_TTL_SECONDS = 300;
    public static function encode(array $payload): string
    {
        $header = self::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payloadSegment = self::base64UrlEncode(json_encode($payload));
        $signature = self::base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payloadSegment}", (string) config('app.key'), true)
        );

        return "{$header}.{$payloadSegment}.{$signature}";
    }

    public static function decodePayload(string $token): ?array
    {
        $parts = explode('.', trim($token));

        if (count($parts) !== 3) {
            return null;
        }

        $decoded = json_decode(self::base64UrlDecode($parts[1]), true);

        return is_array($decoded) ? $decoded : null;
    }

    public static function isJwt(string $token): bool
    {
        return self::decodePayload($token) !== null;
    }

    public static function verifySignature(string $token): bool
    {
        $parts = explode('.', trim($token));

        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payloadSegment, $signature] = $parts;
        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payloadSegment}", (string) config('app.key'), true)
        );

        return hash_equals($expectedSignature, $signature);
    }

    public static function isExpired(string $token, ?int $checkedAt = null): bool
    {
        $payload = self::decodePayload($token);

        if ($payload === null || ! isset($payload['exp'])) {
            return true;
        }

        return ($checkedAt ?? time()) >= (int) $payload['exp'];
    }

    public static function isValidToken(string $token, ?int $checkedAt = null): bool
    {
        return self::verifySignature($token) && ! self::isExpired($token, $checkedAt);
    }

    public static function getExpirationTimestamp(string $token): ?int
    {
        $payload = self::decodePayload($token);

        return isset($payload['exp']) ? (int) $payload['exp'] : null;
    }

    public static function getSubjectId(string $token): ?int
    {
        $payload = self::decodePayload($token);
        $subject = $payload['sub'] ?? $payload['user_id'] ?? $payload['id'] ?? null;

        return $subject !== null ? (int) $subject : null;
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        return (string) base64_decode(strtr($value, '-_', '+/'), true);
    }
}
