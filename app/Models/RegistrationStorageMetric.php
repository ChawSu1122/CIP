<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationStorageMetric extends Model
{
    protected $fillable = [
        'user_id',
        'auth_method',
        'user_name',
        'user_row_bytes',
        'session_row_bytes',
        'token_bytes',
        'total_bytes',
        'total_kb',
    ];

    protected function casts(): array
    {
        return [
            'total_kb' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
