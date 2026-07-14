<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'api_token',
        'auth_method',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function createApiToken(): string
    {
        $now = time();
        $payload = [
            'jti' => bin2hex(random_bytes(16)),
            'iat' => $now,
            'exp' => $now + 20,
        ];

        $token = $this->createJwtToken($payload);

        $this->forceFill(['api_token' => $token])->save();

        return $token;
    }

    public function revokeApiToken(): void
    {
        $this->forceFill(['api_token' => null])->save();
    }

    public static function findUserByToken(?string $token): ?self
    {
        if (blank($token)) {
            return null;
        }

        $user = static::where('api_token', $token)->first();

        if (! $user || ! $user->validateApiToken($token)) {
            return null;
        }

        return $user;
    }

    public function validateApiToken(?string $token): bool
    {
        if (blank($token)) {
            return false;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$headerSegment, $payloadSegment, $signature] = $parts;
        $header = json_decode($this->base64UrlDecode($headerSegment), true);
        $payload = json_decode($this->base64UrlDecode($payloadSegment), true);

        if (! is_array($header) || ! is_array($payload)) {
            return false;
        }

        if (($payload['exp'] ?? 0) < time()) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $headerSegment . '.' . $payloadSegment, config('app.key'));

        return hash_equals($expectedSignature, $signature);
    }

    private function createJwtToken(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $encodedHeader = $this->base64UrlEncode(json_encode($header));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, config('app.key'));

        return $encodedHeader . '.' . $encodedPayload . '.' . $signature;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'), true) ?: '';
    }
}
