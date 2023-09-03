<?php

namespace Tests\Unit\Services;

use App\Services\Factory;
use App\Services\XClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery\MockInterface;
use Tests\TestCase;

class XClientTest extends TestCase
{
    /**
     * @dataProvider requestDataProvider
     */
    public function testRequestJsonSuccess(string $method, int $statusCode, array $header, string $content)
    {
        $url = $this->faker->url;
        $this->instance(Client::class, \Mockery::mock(Client::class, function (MockInterface $mock) use ($statusCode, $header, $content) {
            $mock->shouldReceive('request')
                ->andReturn(
                    new Response(
                        $statusCode,
                        $header,
                        $content
                    )
                );
        }));

        $this->instance(Factory::class, \Mockery::mock(Factory::class, function (MockInterface $mock) {
            $mock->shouldReceive('enableRetries')
                ->andReturnSelf();

            $mock->shouldReceive('make')
                ->andReturn(app(Client::class));

            $mock->shouldReceive('enableLogging');
        }));

        $client = app(XClient::class);
        $response = $client->{$method}($url);

        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals(json_decode($content, true), $response->getData());

        $this->assertDatabaseHas('request_logs', [
            'url' => $url,
            'method' => strtoupper($method),
            'status_code' => $statusCode,
            'is_success' => true,
        ], 'mongodb');
    }

    public function requestDataProvider()
    {
        return [
            [
                'method' => 'GET',
                'statusCode' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'content' => json_encode(['foo' => 'bar']),
            ],
            [
                'method' => 'POST',
                'statusCode' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'content' => json_encode(['foo' => 'bar']),
            ],
        ];
    }
}
