<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExperimentMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'auth_type',
        'action',
        'method',
        'path',
        'duration_ms',
        'memory_usage',
        'query_count',
        'storage_bytes',
        'success',
        'victim_id',
        'victim_name',
        'victim_email',
        'victim_authentication_type',
        'victim_session_id',
        'victim_token',
        'victim_user_agent',
        'attacker_user_agent',
        'attacker_id',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
        ];
    }
}
