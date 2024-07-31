<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use PCDB\Http\Client;
use PCDB\Http\Config;
use PCDB\Services\IndexService;
use PCDB\Services\VectorService;
use PCDB\Models\IndexConfig;
use PCDB\Models\VectorModel;
use PCDB\Models\VectorQuery;

class VectorServiceIntegrationTest extends TestCase
{
    private VectorService $vectorService;
    private IndexService $indexService;
    private string $indexName;

    protected function setUp(): void
    {
        $config = new Config();
        $client = new Client($config);

        $this->indexService = new IndexService($client);
        $this->indexName = 'integration-test-index';

        // Create a test index
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
        $this->indexService->createIndex($indexConfig);

        // Wait for the index to be ready
        $ready = false;
        while (!$ready) {
            $describeResponse = $this->indexService->describeIndex($this->indexName);
            $ready = $describeResponse['status']['ready'] ?? false;
            if (!$ready) {
                sleep(20);
            }
        }

        // Get the custom endpoint for the index
        $listResponse = $this->indexService->listIndexes();
        $host = '';
        foreach ($listResponse['indexes'] as $index) {
            if ($index['name'] === $this->indexName) {
                $host = $index['host'];
                break;
            }
        }
        if (empty($host)) {
            throw new Exception('Custom endpoint not found for the index');
        }

        // Reinitialize the client and vector service with the custom endpoint
        $customClient = new Client($config, null, "https://$host");
        $this->vectorService = new VectorService($customClient);
    }

    protected function tearDown(): void
    {
        // Delete the test index
        $this->indexService->deleteIndex($this->indexName);
    }

    public function testVectorLifecycle(): void
    {
        // Step 1: Upsert Vectors
        $vector1 = new VectorModel('vec1', array_fill(0, 1536, 0.5));
        $vector2 = new VectorModel('vec2', array_fill(0, 1536, 0.3));
        $upsertResponse = $this->vectorService->upsert($this->indexName, [$vector1, $vector2]);
        $this->assertArrayHasKey('upsertedCount', $upsertResponse);
        $this->assertEquals(2, $upsertResponse['upsertedCount']);

        // Step 2: Fetch vectors
        $fetchResponse = $this->vectorService->fetch($this->indexName, ['vec1', 'vec2']);
        $this->assertArrayHasKey('vectors', $fetchResponse);

        // Step 3: Query vectors
        $query = new VectorQuery(
            array_fill(0, 1536, 0.1),
            2,
            null,
            ['genre' => ['$eq' => 'comedy']],
            true,
            true
        );
        $queryResponse = $this->vectorService->query($this->indexName, $query);
        $this->assertArrayHasKey('matches', $queryResponse);

        // Step 4: Delete vectors
        $deleteResponse = $this->vectorService->deleteVectors($this->indexName, ['vec1', 'vec2']);
        $this->assertIsArray($deleteResponse);
        $this->assertEmpty($deleteResponse);
    }
}
