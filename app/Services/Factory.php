<?php

namespace App\Services;

use App\Services\Traits\HasOptions;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

class Factory
{
    use HasOptions;

    private HandlerStack $handler;
    private MockHandler $mocking;

    private LoggerInterface $logger;

    private Client $client;

    public function __construct()
    {
        $this->bootHasOptions();
        $this->reset();
    }

    public function reset(): self
    {
        $this->handler = HandlerStack::create($this->mocking ?? null);

        return $this;
    }

    public function enableMocking(): self
    {
        $this->mocking = app(MockHandler::class);
        $this->reset();

        return $this;
    }

    public function addMockResponse(
        int    $status = 200,
        array  $headers = [],
               $body = null,
        string $version = '1.1',
        string $reason = null
    ): self
    {
        if (!isset($this->mocking)) {
            throw  new \Exception('Mocking is not enabled');
        }

        $this->mocking->append(
            new Response($status, $headers, $body, $version, $reason)
        );

        return $this;
    }

    public function addMockRequestException(
        string            $message,
        RequestInterface  $request,
        ResponseInterface $response = null,
        Throwable         $previous = null,
    ): self
    {
        if (!isset($this->mocking)) {
            throw  new \Exception('Mocking is not enabled');
        }

        $this->mocking->append(
            new RequestException($message, $request, $response, $previous)
        );

        return $this;
    }

    public function enableRetries(int $maxRetries = 3, int $delayInSec = 1, int $minErrorCode = 500): self
    {
        $decider = function ($retries, $_, $response) use ($maxRetries, $minErrorCode) {
            return $retries < $maxRetries
                && $response instanceof ResponseInterface
                && $response->getStatusCode() >= $minErrorCode;
        };

        $increasingDelay = fn($attempt) => $attempt * $delayInSec * 1000;

        return $this->withMiddleware(
            Middleware::retry($decider, $increasingDelay),
            'retry'
        );
    }

    public function withMiddleware(callable $middleware, string $name = ''): self
    {
        $this->handler->push($middleware, $name);

        return $this;
    }

    public function enableLogging(
        LoggerInterface $logger,
        string          $format = MessageFormatter::SHORT,
        string          $level = LogLevel::INFO
    ): self
    {
        $this->logger = $logger;

        return $this->withMiddleware(
            Middleware::log($this->logger, new MessageFormatter($format), $level),
            'log'
        );
    }

    public function getHistory($client): array
    {
        return $this->history[spl_object_id($client)] ?? [];
    }

    public function make(): Client
    {
        $this->client = new Client(array_merge([
            'handler' => $this->handler,
        ], $this->getOptionsArray()));

        if (isset($this->mocking)) {
            /**
             * @link https://docs.guzzlephp.org/en/stable/testing.html#history-middleware
             */
            $this->history[$id = spl_object_id($this->client)] = [];
            $this->withMiddleware(
                Middleware::history($this->history[$id]),
                'fake_history'
            );
        }

        $this->reset();

        return $this->client;
    }
}
