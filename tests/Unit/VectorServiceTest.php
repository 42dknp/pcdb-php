<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PCDB\Http\Client;
use PCDB\Models\VectorModel;
use PCDB\Models\VectorQuery;
use PCDB\Services\VectorService;
use PCDB\Validation\PCDBValidator;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * VectorServiceTest
 *
 * Unit tests for the VectorService.
 */
class VectorServiceTest extends TestCase
{
    private VectorService $vectorService;
    private MockObject $client;
    private MockObject $validator;

    protected function setUp(): void
    {
        // Create mocks for Client and PCDBValidator
        $this->client = $this->createMock(Client::class);
        $this->validator = $this->createMock(PCDBValidator::class);

        // Inject both the client and the validator into the VectorService
        $this->vectorService = new VectorService($this->client, $this->validator);
    }

    /**
     * Data provider for vectors.
     *
     * @return array
     */
    public function vectorProvider(): array
    {
        return [
            'simple-vector' => [
                [
                    new VectorModel('vec1', [0.1, 0.2, 0.3])
                ]
            ],
            'vector-with-metadata' => [
                [
                    new VectorModel('vec2', [0.4, 0.5, 0.6], null, ['genre' => 'drama'])
                ]
            ],
            'vector-with-sparse-values' => [
                [
                    new VectorModel('vec3', [0.7, 0.8, 0.9], ['indices' => [1, 2], 'values' => [0.1, 0.2]])
                ]
            ],
        ];
    }

    /**
     * @dataProvider vectorProvider
     */
    public function testUpsert(array $vectors): void
    {
        // Mock the validator to expect the validate call
        $this->validator->expects($this->atLeastOnce())
            ->method('validate')
            ->withAnyParameters();

        // Mock the client's request method
        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', '/vectors/upsert', [
                'vectors' => array_map(function ($vector) {
                    $formatted = [
                        'id' => $vector->id,
                        'values' => $vector->values,
                    ];
                    if (!empty($vector->sparseValues)) {
                        $formatted['sparseValues'] = [
                            'indices' => array_keys($vector->sparseValues),
                            'values' => array_values($vector->sparseValues),
                        ];
                    }
                    if (!empty($vector->metadata)) {
                        $formatted['metadata'] = $vector->metadata;
                    }
                    return $formatted;
                }, $vectors)
            ])
            ->willReturn(['upsertedCount' => count($vectors)]);

        $response = $this->vectorService->upsert('test-index', $vectors);
        $this->assertIsArray($response);
    }

    public function testFetch(): void
    {
        $vectorIds = ['vec1', 'vec2'];

        // Mock the validator
        $this->validator->expects($this->atLeastOnce())
            ->method('validate')
            ->withAnyParameters();

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/vectors/fetch?ids=vec1&ids=vec2')
            ->willReturn(['vectors' => $vectorIds]);

        $response = $this->vectorService->fetch('test-index', $vectorIds);
        $this->assertIsArray($response);
    }

    public function testQuery(): void
    {
        $query = new VectorQuery([0.1, 0.2, 0.3], 5, null, ['genre' => 'drama'], true, true);

        // Mock the validator
        $this->validator->expects($this->atLeastOnce())
            ->method('validate')
            ->withAnyParameters();

        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', '/query', [
                'vector' => $query->vector,
                'topK' => $query->topK,
                'filter' => $query->filter,
                'includeValues' => $query->includeValues,
                'includeMetadata' => $query->includeMetadata,
            ])
            ->willReturn(['matches' => []]);

        $response = $this->vectorService->query('test-index', $query);
        $this->assertIsArray($response);
    }

    public function testUpdate(): void
    {
        // Mock the validator
        $this->validator->expects($this->atLeastOnce())
            ->method('validate')
            ->withAnyParameters();

        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', '/vectors/update', [
                'id' => 'vec1',
                'values' => [0.1, 0.2, 0.3],
                'setMetadata' => ['genre' => 'comedy']
            ])
            ->willReturn(['updated' => 1]);

        $response = $this->vectorService->update('test-index', 'vec1', [0.1, 0.2, 0.3], ['genre' => 'comedy']);
        $this->assertIsArray($response);
    }

    public function testListVectorIDs(): void
    {
        $query = http_build_query([
            'limit' => 100,
        ]);

        // Mock the validator
        $this->validator->expects($this->atLeastOnce())
            ->method('validate')
            ->withAnyParameters();

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/vectors/list?' . $query)
            ->willReturn(['vectors' => []]);

        $response = $this->vectorService->listVectorIDs('test-index');
        $this->assertIsArray($response);
    }

    public function testDeleteVectors(): void
    {
        $vectorIds = ['vec1', 'vec2'];

        // Mock the validator
        $this->validator->expects($this->atLeastOnce())
            ->method('validate')
            ->withAnyParameters();

        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', '/vectors/delete', [
                'ids' => $vectorIds
            ])
            ->willReturn(['deletedCount' => count($vectorIds)]);

        $response = $this->vectorService->deleteVectors('test-index', $vectorIds);
        $this->assertIsArray($response);
    }
}
