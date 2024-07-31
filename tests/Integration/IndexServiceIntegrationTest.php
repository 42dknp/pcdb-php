<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use PCDB\Http\Client;
use PCDB\Http\Config;
use PCDB\Services\IndexService;
use PCDB\Models\IndexConfig;

class IndexServiceIntegrationTest extends TestCase
{
    private IndexService $indexService;
    private IndexService $indexService2;
    private string $indexName;

    protected function setUp(): void
    {
        $config = new Config();
        $client = new Client($config);

        $this->indexService = new IndexService($client);
        $this->indexName = 'integration-test-index';
    }

    public function testIndexLifecycle(): void
    {
        // Step 1: Create Index
        $indexConfig = new IndexConfig(
            $this->indexName,
            'cosine',
            1536,
            [
                'serverless' => [
                    'cloud' => 'aws',
                    'region' => 'us-east-1'
                ],
                'replicas' => 1,
                'shards' => 1,
            ]
        );
        $createResponse = $this->indexService->createIndex($indexConfig);
        $this->assertArrayHasKey('name', $createResponse);
        $this->assertEquals($this->indexName, $createResponse['name']);

        // Step 2: Wait for Index to be Ready
        $ready = false;
        while (!$ready) {
            $describeResponse = $this->indexService->describeIndex($this->indexName);
            $ready = $describeResponse['status']['ready'] ?? false;
            if (!$ready) {
                sleep(20); // Sleep before checking again
            }
        }

        // Step 3: Describe Index
        $describeResponse = $this->indexService->describeIndex($this->indexName);
        $this->assertArrayHasKey('name', $describeResponse);
        $this->assertEquals($this->indexName, $describeResponse['name']);
        $this->assertArrayHasKey('metric', $describeResponse);
        $this->assertArrayHasKey('dimension', $describeResponse);

        // Step 4: List Indexes
        $listResponse = $this->indexService->listIndexes();
        $this->assertContains($this->indexName, array_column($listResponse['indexes'], 'name'));

        // Step 5: Delete Index
        $deleteResponse = $this->indexService->deleteIndex($this->indexName);
        $this->assertIsArray($deleteResponse);
        $this->assertEmpty($deleteResponse);
    }
}
