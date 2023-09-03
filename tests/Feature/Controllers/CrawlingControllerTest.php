<?php

namespace Feature\Controllers;

use App\Services\Factory;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery\MockInterface;
use Tests\TestCase;

class CrawlingControllerTest extends TestCase
{
    /**
     * @dataProvider crawlingDataProvider
     */
    public function testCrawlingWithJson(string $method, int $statusCode, array $headers, string $content)
    {
        $url = $this->faker->unique()->url;
        $this->instance(Client::class, \Mockery::mock(Client::class, function (MockInterface $mock) use ($statusCode, $headers, $content) {
            $mock->shouldReceive('request')
                ->andReturn(
                    new Response(
                        $statusCode,
                        $headers,
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

        $response = $this->post('api/v1/crawling', [
            'url' => $url,
            'method' => $method,
            'payload' => [
                'foo' => 'bar'
            ],
            'options' => [],
        ])->assertStatus(200);

        $this->assertEquals(json_decode($content, true), $response->json());

        $this->assertDatabaseHas('request_logs', [
            'url' => $url,
            'method' => $method,
            'status_code' => $statusCode,
            'is_success' => true,
            'response' => '{"foo":"bar"}'
        ], 'mongodb');
    }

    public function crawlingDataProvider()
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
                'statusCode' => 201,
                'headers' => ['Content-Type' => 'application/json'],
                'content' => json_encode(['foo' => 'bar']),
            ],
            [
                'method' => 'PUT',
                'statusCode' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'content' => json_encode(['foo' => 'bar']),
            ],
            [
                'method' => 'DELETE',
                'statusCode' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'content' => json_encode(['foo' => 'bar']),
            ],
        ];
    }

    public function testCrawlingWithText()
    {
        $url = $this->faker->unique()->url;
        $this->instance(Client::class, \Mockery::mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('request')
                ->andReturn(
                    new Response(
                        201,
                        [],
                        'foo'
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

        $response = $this->post('api/v1/crawling', [
            'url' => $url,
            'method' => 'GET',
            'payload' => [],
            'options' => [],
        ])->assertStatus(200);

        $this->assertEquals('foo', $response->getContent());

        $this->assertDatabaseHas('request_logs', [
            'url' => $url,
            'method' => 'GET',
            'status_code' => 201,
            'is_success' => true,
            'response' => 'foo'
        ], 'mongodb');
    }

    public function testQueueCrawling()
    {
        $url = $this->faker->unique()->url;
        $this->instance(Client::class, \Mockery::mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('request')
                ->andReturn(
                    new Response(
                        201,
                        [],
                        'foo'
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

        $this->post('api/v1/crawling/queues', [
            'url' => $url,
            'method' => 'GET',
            'payload' => [],
            'options' => [],
        ])->assertStatus(302);

        $this->post('api/v1/crawling/queues', [
            'url' => $url,
            'method' => 'GET',
            'payload' => [],
            'options' => [],
            'callback_url' => 'http://localhost:8000/api/v1/crawling',
        ])->assertStatus(204);

        $this->assertDatabaseHas('queues', [
            'url' => $url,
            'method' => 'GET',
            'status' => 'pending',
            'callback_url' => 'http://localhost:8000/api/v1/crawling',
        ], 'mongodb');
    }
}
