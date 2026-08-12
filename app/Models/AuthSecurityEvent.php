<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthSecurityEvent extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'status',
        'message',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
