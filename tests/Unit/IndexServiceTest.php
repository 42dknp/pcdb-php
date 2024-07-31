<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PCDB\Http\Client;
use PCDB\Exceptions\PCDBException;
use PCDB\Models\IndexConfig;
use PCDB\Services\IndexService;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * IndexServiceTest
 *
 * Unit tests for the IndexService.
 */
class IndexServiceTest extends TestCase
{
    private IndexService $indexService;
    private MockObject $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->indexService = new IndexService($this->client);
    }

    /**
     * Data provider for index configurations.
     *
     * @return array
     */
    public function indexConfigProvider(): array
    {
        return [
            'pod-based' => [
                new IndexConfig('test-index', 'cosine', 128, [
                    'pod' => [
                        'environment' => 'us-west1',
                        'pod_type' => 'p1.x1',
                        'pods' => 1
                    ]
                ])
            ],
            'serverless' => [
                new IndexConfig('test-index', 'cosine', 128, [
                    'serverless' => [
                        'cloud' => 'aws',
                        'region' => 'us-east-1'
                    ]
                ])
            ],
        ];
    }

    /**
     * @dataProvider indexConfigProvider
     */
    public function testCreateIndex(IndexConfig $config): void
    {
        $spec = [];
        if (isset($config->environment)) {
            $spec['pod'] = [
                'environment' => $config->environment,
                'pod_type' => $config->podType,
                'pods' => $config->pods,
            ];
        } else {
            $spec['serverless'] = [
                'cloud' => $config->cloud,
                'region' => $config->region,
            ];
        }

        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', '/indexes', [
                'name' => $config->indexName,
                'dimension' => $config->dimension,
                'metric' => $config->metric,
                'spec' => array_merge($spec, [
                    'replicas' => $config->replicas,
                    'shards' => $config->shards
                ]),
                'deletion_protection' => $config->deletionProtection ?? 'disabled',
            ])
            ->willReturn(['index' => 'created']);

        $response = $this->indexService->createIndex($config);
        $this->assertIsArray($response);
    }

    public function testDeleteIndex(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('DELETE', '/indexes/test-index')
            ->willReturn([]);

        $response = $this->indexService->deleteIndex('test-index');
        $this->assertIsArray($response);
    }

    public function testListIndexes(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/indexes')
            ->willReturn(['indexes' => []]);

        $response = $this->indexService->listIndexes();
        $this->assertIsArray($response);
    }

    public function testDescribeIndex(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/indexes/test-index')
            ->willReturn(['name' => 'test-index']);

        $response = $this->indexService->describeIndex('test-index');
        $this->assertIsArray($response);
    }

    /**
     * @dataProvider indexConfigProvider
     */
    public function testUpdateIndexConfig(IndexConfig $config): void
    {
        $payload = [];
        if (isset($config->environment)) {
            $payload['spec'] = [
                'pod' => [
                    'environment' => $config->environment,
                    'pod_type' => $config->podType,
                    'pods' => $config->pods,
                ],
                'replicas' => $config->replicas,
            ];
        } else {
            $payload['deletion_protection'] = $config->deletionProtection;
        }

        if (!empty($config->deletionProtection)) {
            $payload['deletion_protection'] = $config->deletionProtection;
        }

        $this->client->expects($this->once())
            ->method('request')
            ->with('PATCH', '/indexes/test-index', $payload)
            ->willReturn(['index' => 'updated']);

        $response = $this->indexService->updateIndexConfig('test-index', $config);
        $this->assertIsArray($response);
    }

    public function testDescribeIndexStats(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', '/describe_index_stats', ['index_name' => 'test-index'])
            ->willReturn(['totalVectors' => 1000, 'indexSize' => 200]);

        $response = $this->indexService->describeIndexStats('test-index');
        $this->assertIsArray($response);
    }

    public function testCreateIndexThrowsExceptionOnEmptyName(): void
    {
        $this->expectException(PCDBException::class);
        $this->expectExceptionMessage('Index name cannot be empty');

        $config = new IndexConfig('', 'cosine', 128, [
            'pod' => [
                'environment' => 'us-west1',
                'pod_type' => 'p1.x1',
                'pods' => 1
            ]
        ]);

        $this->indexService->createIndex($config);
    }
}
