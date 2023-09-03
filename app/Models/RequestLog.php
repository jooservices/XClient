<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class RequestLog extends Model
{
    protected $fillable = [
        'method',
        'url',
        'options',
        'payload',
        'response',
        'status_code',
        'started_at',
        'completed_at',
        'is_success',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'string',
        'status_code' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_success' => 'boolean',
    ];

    protected $connection = 'mongodb';
}
