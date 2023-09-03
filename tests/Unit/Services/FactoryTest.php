<?php

namespace Tests\Unit\Services;

use App\Services\Factory;
use App\Services\Middlewares\Logging;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use OutOfBoundsException;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    private Factory $factory;

    public function setup(): void
    {
        parent::setUp();

        $this->factory = app(Factory::class);
    }

    public function testEnableMockingWithoutQueue()
    {
        $this->expectException(OutOfBoundsException::class);
        $client = $this->factory
            ->enableMocking()
            ->make();

        $client->request('GET', $this->faker->url);
    }

    public function testMockingException()
    {
        $this->expectException(RequestException::class);
        $client = $this->factory
            ->enableMocking()
            ->addMockRequestException(
                'Error Communicating with Server',
                new Request('GET', $this->faker->url),
            )->make();

        $client->request('GET', $this->faker->url);
    }

    public function testMockingRequestSuccess()
    {
        $client = $this->factory
            ->enableMocking()
            ->addMockResponse(
                200,
                [],
                json_encode(['foo' => 'bar'])
            )
            ->addMockResponse(
                201,
                [],
                json_encode(['foo' => 'bar'])
            )
            ->make();

        $response = $client->request('GET', $this->faker->url);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['foo' => 'bar'], json_decode($response->getBody()->getContents(), true));

        $response = $client->request('GET', $this->faker->url);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(['foo' => 'bar'], json_decode($response->getBody()->getContents(), true));

        $this->assertCount(2, $this->factory->getHistory($client));

        $this->expectException(OutOfBoundsException::class);
        $client->request('GET', $this->faker->url);
    }

    public function testRequestFailed()
    {
        $this->expectException(ServerException::class);
        $client = $this->factory
            ->enableMocking()
            ->addMockResponse(
                500,
                [],
                json_encode(['foo' => 'bar'])
            )
            ->make();

        $response = $client->request('GET', $this->faker->url);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(['foo' => 'bar'], json_decode($response->getBody()->getContents(), true));
    }
}
