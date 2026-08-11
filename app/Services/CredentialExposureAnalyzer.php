<?php

namespace App\Services;

use App\Models\User;
use App\Support\JwtHelper;
use Illuminate\Support\Facades\DB;

class CredentialExposureAnalyzer
{
    private const IDENTITY_CLAIMS = [
        'sub',
        'user_id',
        'id',
        'uid',
        'email',
        'mail',
        'role',
        'roles',
        'name',
        'username',
        'exp',
        'iat',
        'nbf',
        'iss',
        'aud',
        'jti',
    ];

    public function analyzeSession(string $sessionId): array
    {
        $sessionId = trim($sessionId);
        $exposedFields = $this->extractEmbeddedIdentityFields($sessionId);

        $sessionExists = false;
        $payloadBytes = null;
        $dbUserId = null;

        if (config('session.driver') === 'database') {
            $session = DB::table(config('session.table'))->where('id', $sessionId)->first();
            $sessionExists = $session !== null;

            if ($session) {
                $payloadBytes = strlen((string) $session->payload);
                $dbUserId = $session->user_id;
            }
        }

        return [
            'found' => $sessionExists,
            'analyzed' => $sessionId !== '',
            'exposed_field_count' => count($exposedFields),
            'exposed_fields' => $exposedFields,
            'session_id_length' => strlen($sessionId),
            'payload_bytes' => $payloadBytes,
            'user_id' => $dbUserId,
        ];
    }

    public function analyzeToken(string $token): array
    {
        $token = trim($token);
        $payload = JwtHelper::decodePayload($token);
        $user = User::where('api_token', $token)->first();

        if ($payload !== null) {
            $exposedFields = $this->identityClaimsFromPayload($payload);

            return [
                'found' => $user !== null || $payload !== [],
                'analyzed' => $token !== '',
                'exposed_field_count' => count($exposedFields),
                'exposed_fields' => $exposedFields,
                'claims' => $payload,
                'token_length' => strlen($token),
                'user_id' => $payload['sub'] ?? $payload['user_id'] ?? $payload['id'] ?? $user?->id,
                'user_email' => $payload['email'] ?? $user?->email,
                'is_jwt' => true,
            ];
        }

        $exposedFields = $this->extractEmbeddedIdentityFields($token);

        return [
            'found' => $user !== null,
            'analyzed' => $token !== '',
            'exposed_field_count' => count($exposedFields),
            'exposed_fields' => $exposedFields,
            'claims' => [],
            'token_length' => strlen($token),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'is_jwt' => false,
        ];
    }

    private function extractEmbeddedIdentityFields(string $credential): array
    {
        $fields = [];

        $jwtPayload = JwtHelper::decodePayload($credential);
        if ($jwtPayload !== null) {
            return $this->identityClaimsFromPayload($jwtPayload);
        }

        $jsonDecoded = json_decode($credential, true);
        if (is_array($jsonDecoded)) {
            return $this->identityClaimsFromPayload($jsonDecoded);
        }

        $base64Decoded = base64_decode($credential, true);
        if ($base64Decoded !== false) {
            $jsonFromBase64 = json_decode($base64Decoded, true);
            if (is_array($jsonFromBase64)) {
                return $this->identityClaimsFromPayload($jsonFromBase64);
            }
        }

        foreach (self::IDENTITY_CLAIMS as $claim) {
            if ($this->credentialContainsFieldReference($credential, $claim)) {
                $fields[] = $claim;
            }
        }

        return array_values(array_unique($fields));
    }

    private function identityClaimsFromPayload(array $payload): array
    {
        $fields = [];

        foreach (array_keys($payload) as $key) {
            $normalizedKey = strtolower((string) $key);

            if (in_array($normalizedKey, self::IDENTITY_CLAIMS, true)) {
                $fields[] = $normalizedKey;
            }
        }

        return array_values(array_unique($fields));
    }

    private function credentialContainsFieldReference(string $credential, string $field): bool
    {
        return (bool) preg_match('/(?:^|[\W_])'.preg_quote($field, '/').'(?:[\W_]|=|:|$)/i', $credential);
    }
}
