<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PCDB\Http\Client;
use PCDB\Exceptions\PCDBException;
use PCDB\Models\Collection;
use PCDB\Services\CollectionService;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client as HttpClient;

/**
 * CollectionServiceTest
 *
 * Unit tests for the CollectionService.
 */
class CollectionServiceTest extends TestCase
{
    private CollectionService $collectionService;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $config = new PCDB\Http\Config();
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client = $this->getMockBuilder(Client::class)
                       ->setConstructorArgs([$config, null])
                       ->onlyMethods(['request'])
                       ->getMock();

        $client->method('request')
               ->will($this->returnCallback(function($method, $endpoint, $data) use ($httpClient) {
                   return json_decode($httpClient->request($method, $endpoint, ['json' => $data])->getBody()->getContents(), true);
               }));

        $this->collectionService = new CollectionService($client);
    }

    public function testCreateCollection(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode(['status' => 'success'])));
        $collection = new Collection('test-collection', 'Test Description');
        $response = $this->collectionService->createCollection($collection);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
    }

    public function testDeleteCollection(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode(['status' => 'success'])));
        $response = $this->collectionService->deleteCollection('test-collection');
        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
    }

    public function testListCollections(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode(['collections' => [['name' => 'test-collection']]])));
        $response = $this->collectionService->listCollections();
        $this->assertIsArray($response);
        $this->assertArrayHasKey('collections', $response);
        $this->assertContains('test-collection', array_column($response['collections'], 'name'));
    }

    public function testDescribeCollection(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode(['name' => 'test-collection'])));
        $response = $this->collectionService->describeCollection('test-collection');
        $this->assertIsArray($response);
        $this->assertArrayHasKey('name', $response);
        $this->assertEquals('test-collection', $response['name']);
    }
}
