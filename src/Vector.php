<?php declare(strict_types=1);

namespace PCDB;

use PCDB\Http\Client;
use PCDB\Http\Config;
use PCDB\Services\VectorService;
use PCDB\Models\VectorModel;
use PCDB\Models\VectorQuery;
use PCDB\Exceptions\PCDBException;

/**
 * Vector
 *
 * Simplified interface for vector operations.
 */
class Vector
{
    private VectorService $_vectorService;

    /**
     * Vector constructor.
     *
     * Initializes the VectorService with the provided configuration.
     *
     * @param Config|null $config         Configuration object. If null, a new Config will be created.
     * @param string|null $customEndpoint Custom endpoint URL. If null, the default endpoint will be used.
     * 
     * @throws PCDBException
     */
    public function __construct(?Config $config = null, ?string $customEndpoint = null)
    {
        $config = $config ?? new Config();
        $client = new Client($config, null, $customEndpoint);
        $this->_vectorService = new VectorService($client);
    }

    /**
     * Upsert vectors into the specified index.
     *
     * @param string             $indexName Name of the index.
     * @param array<VectorModel> $vectors   Array of vectors to upsert.
     * @param string|null        $namespace Namespace for the upsert operation.
     * 
     * @return array<string, mixed> Response from the API.
     * @throws PCDBException
     */
    public function upsertVectors(string $indexName, array $vectors, ?string $namespace = null): array
    {
        return $this->_vectorService->upsert($indexName, $vectors, $namespace);
    }

    /**
     * Fetch vectors by their IDs from the specified index.
     *
     * @param string        $indexName Name of the index.
     * @param array<string> $vectorIds Array of vector IDs to fetch.
     * @param string|null   $namespace Namespace for the fetch operation.
     * 
     * @return array<string, mixed> Response from the API.
     * @throws PCDBException
     */
    public function fetchVectors(string $indexName, array $vectorIds, ?string $namespace = null): array
    {
        return $this->_vectorService->fetch($indexName, $vectorIds, $namespace);
    }

    /**
     * Query vectors in the specified index.
     *
     * @param string      $indexName Name of the index.
     * @param VectorQuery $query     Query object.
     * 
     * @return array<string, mixed> Response from the API.
     * @throws PCDBException
     */
    public function queryVectors(string $indexName, VectorQuery $query): array
    {
        return $this->_vectorService->query($indexName, $query);
    }

    /**
     * Update vectors in the specified index.
     *
     * @param string               $indexName Name of the index.
     * @param string               $vectorId  ID of the vector to update.
     * @param array<int, float>    $values    New values for the vector.
     * @param array<string, mixed> $metadata  New metadata to set for the vector.
     * @param string|null          $namespace Namespace for the update operation.
     * 
     * @return array<string, mixed> Response from the API.
     * @throws PCDBException
     */
    public function updateVector(string $indexName, string $vectorId, array $values, array $metadata, ?string $namespace = null): array
    {
        return $this->_vectorService->update($indexName, $vectorId, $values, $metadata, $namespace);
    }

    /**
     * List vector IDs in the specified index.
     *
     * @param string      $indexName       Name of the index.
     * @param string|null $namespace       Namespace for the list operation.
     * @param string|null $prefix          Prefix to filter the vector IDs.
     * @param int|null    $limit           Number of IDs to return.
     * @param string|null $paginationToken Token for pagination.
     * 
     * @return array<string, mixed> Response from the API.
     * @throws PCDBException
     */
    public function listVectorIDs(string $indexName, ?string $namespace = null, ?string $prefix = null, ?int $limit = 100, ?string $paginationToken = null): array
    {
        return $this->_vectorService->listVectorIDs($indexName, $namespace, $prefix, $limit, $paginationToken);
    }

    /**
     * Delete vectors from the specified index by their IDs.
     *
     * @param string            $indexName Name of the index.
     * @param array<int, mixed> $vectorIds Array of vector IDs to delete.
     * @param string|null       $namespace Namespace for the delete operation.
     * 
     * @return array<string, mixed> Response from the API.
     * @throws PCDBException
     */
    public function deleteVectors(string $indexName, array $vectorIds, ?string $namespace = null): array
    {
        return $this->_vectorService->deleteVectors($indexName, $vectorIds, $namespace);
    }

    /**
     * Delete all vectors in the specified namespace.
     *
     * @param string $indexName Name of the index.
     * @param string $namespace Namespace to delete.
     * 
     * @return array<string, mixed> Response from the API.
     * @throws PCDBException
     */
    public function deleteNamespace(string $indexName, string $namespace): array
    {
        return $this->_vectorService->deleteNamespace($indexName, $namespace);
    }
}
