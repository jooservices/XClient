<?php

namespace App\Services\Loggers;

use App\Models\Log;
use Psr\Log\LoggerInterface;

class DatabaseLog implements LoggerInterface
{

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        Log::create([
            'ip' => request()->ip(),
            'level' => $level,
            'message' => $message,
            'context' => $context
        ]);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }
}
