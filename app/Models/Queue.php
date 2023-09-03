<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = [
        'url',
        'method',
        'payload',
        'options',
        'callback_url',
        'status',

    ];

    protected $casts = [
        'url' => 'string',
        'method' => 'string',
        'payload' => 'array',
        'options' => 'array',
        'callback_url' => 'string',
        'status' => 'string',

    ];

    protected $connection = 'mongodb';

    public const STATUS_PENDING = 'pending';
}
