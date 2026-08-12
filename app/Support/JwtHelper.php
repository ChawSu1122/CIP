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
