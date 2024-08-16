<?php

declare(strict_types=1);

namespace PCDB\Services;

use PCDB\Http\Client;
use PCDB\Models\VectorQuery;
use PCDB\Models\VectorModel;
use PCDB\Validation\PCDBValidator;
use PCDB\Exceptions\PCDBException;

/**
 * VectorService
 *
 * Provides methods to interact with PCDB vectors.
 */
class VectorService
{
    private Client $_client;

    private PCDBValidator $_validator;

    /**
     * VectorService constructor.
     *
     * @param Client $_client PCDB client.
     */
    public function __construct(Client $_client, ?PCDBValidator $_validator = null)
    {
        $this->_client = $_client;
        $this->_validator = $_validator ?? new PCDBValidator(new \JsonSchema\Validator());
    }

    /**
     * Upserts vectors into the specified index.
     *
     * @param string             $indexName Name of the index.
     * @param array<VectorModel> $vectors   Array of vectors to upsert.
     * @param string|null        $namespace Namespace for the upsert operation.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function upsert(string $indexName, array $vectors, ?string $namespace = null): array
    {
        $this->_validator->checkNonEmptyValue($indexName, 'Index name');
    
        $formattedVectors = array_map(
            function (VectorModel $vector) {
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
            }, $vectors
        );
    
        $payload = ['vectors' => $formattedVectors];
    
        if ($namespace) {
            $payload['namespace'] = $namespace;
        }
    
        $this->_validator->validate($payload, 'Vectors/upsert_vectors_request_schema.json');

        $response = $this->_client->request('POST', "/vectors/upsert", $payload);

        $this->_validator->validate($response, 'Vectors/upsert_vectors_response_schema.json');

        return $response;
    }

    /**
     * Fetches vectors by their IDs from the specified index.
     *
     * @param string        $indexName Name of the index.
     * @param array<string> $vectorIds Array of vector IDs to fetch.
     * @param string|null   $namespace Namespace for the fetch operation.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function fetch(string $indexName, array $vectorIds, ?string $namespace = null): array
    {
        $this->_validator->checkNonEmptyValue($indexName, 'Index name');

        $query = '';

        // Prepare the payload and query string in a single loop
        $payload = ['ids' => $vectorIds];
        $queryParts = array_map(fn($id) => 'ids=' . urlencode($id), $vectorIds);

        if ($namespace) {
            $payload['namespace'] = $namespace;
            $queryParts[] = 'namespace=' . urlencode($namespace);
        }

        $query = implode('&', $queryParts);

        $this->_validator->validate($payload, 'Vectors/fetch_vectors_request_schema.json');

        $response = $this->_client->request('GET', "/vectors/fetch?$query");

        $this->_validator->validate($response, 'Vectors/fetch_vectors_response_schema.json');

        return $response;
    }

    /**
     * Queries vectors in the specified index.
     *
     * @param string      $indexName Name of the index.
     * @param VectorQuery $query     Query object.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function query(string $indexName, VectorQuery $query): array
    {
        $this->_validator->checkNonEmptyValue($indexName, 'Index name');

        $payload = [
            'vector' => $query->vector,
            'topK' => $query->topK,
            'includeValues' => $query->includeValues,
            'includeMetadata' => $query->includeMetadata,
        ];

        if (!empty($query->filter)) {
            $payload['filter'] = $query->filter;
        }

        if (!empty($query->namespace)) {
            $payload['namespace'] = $query->namespace;
        }

        $this->_validator->validate($payload, 'Vectors/query_vectors_request_schema.json');

        $response = $this->_client->request('POST', '/query', $payload);

        $this->_validator->validate($response, 'Vectors/query_vectors_response_schema.json');

        return $response;
    }

    /**
     * Updates vectors in the specified index.
     *
     * @param string               $indexName Name of the index.
     * @param string               $vectorId  ID of the vector to update.
     * @param array<int, float>    $values    New values for the vector.
     * @param array<string, mixed> $metadata  New metadata to set for the vector.
     * @param string|null          $namespace Namespace for the update operation.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function update(string $indexName, string $vectorId, array $values, array $metadata, ?string $namespace = null): array
    {
        $this->_validator->checkNonEmptyValue($indexName, 'Index name');
        $this->_validator->checkNonEmptyValue($vectorId, 'Vector ID');

        $payload = [
            'id' => $vectorId,
            'values' => $values,
            'setMetadata' => $metadata,
        ];

        if (!empty($namespace)) {
            $payload['namespace'] = $namespace;
        }

        $this->_validator->validate($payload, 'Vectors/update_vector_request_schema.json');

        $response = $this->_client->request('POST', '/vectors/update', $payload);

        $this->_validator->validate($response, 'Vectors/empty_response_schema.json');

        return $response;

    }

    /**
     * Lists vector IDs in the specified index.
     *
     * @param string      $indexName       Name of the index.
     * @param string|null $namespace       Namespace for the list operation.
     * @param string|null $prefix          Prefix to filter the vector IDs.
     * @param int|null    $limit           Number of IDs to return.
     * @param string|null $paginationToken Token for pagination.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function listVectorIDs(string $indexName, ?string $namespace = null, ?string $prefix = null, ?int $limit = 100, ?string $paginationToken = null): array 
    {
        $this->_validator->checkNonEmptyValue($indexName, 'Index name');

        $queryParams = array_filter(
            [
                'namespace' => $namespace,
                'prefix' => $prefix,
                'limit' => $limit,
                'pagination_token' => $paginationToken,
            ]
        );
        
        $query = http_build_query($queryParams);
        
        $payload = $queryParams;

        $this->_validator->validate($payload, 'Vectors/list_vector_ids_request_schema.json');

        $response = $this->_client->request('GET', "/vectors/list?$query");
    
        $this->_validator->validate($response, 'Vectors/list_vector_ids_response_schema.json');
    
        return $response;
    }

    /**
     * Deletes vectors from the specified index by their IDs.
     *
     * @param string           $indexName Name of the index.
     * @param array<int,mixed> $vectorIds Array of vector IDs to delete.
     * @param string|null      $namespace Namespace for the delete operation.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function deleteVectors(string $indexName, array $vectorIds, ?string $namespace = null): array
    {
        $this->_validator->checkNonEmptyValue($indexName, 'Index name');

        $payload = ['ids' => $vectorIds];

        if (!empty($namespace)) {
            $payload['namespace'] = $namespace;
        }

        $this->_validator->validate($payload, 'Vectors/delete_vectors_request_schema.json');

        $response = $this->_client->request('POST', '/vectors/delete', $payload);
    
        $this->_validator->validate($response, 'Vectors/empty_response_schema.json');
    
        return $response;
    }

    /**
     * Deletes all vectors in the specified namespace.
     *
     * @param string $indexName Name of the index.
     * @param string $namespace Namespace to delete.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function deleteNamespace(string $indexName, string $namespace): array
    {
        $this->_validator->checkNonEmptyValue($indexName, 'Index name');
        $this->_validator->checkNonEmptyValue($namespace, 'Namespace');

        $vectorIds = $this->listVectorIDs($indexName, $namespace)['ids'] ?? [];

        if (!is_array($vectorIds)) {
            throw new PCDBException('Invalid response from listVectorIDs: expected array of strings');
        }

        foreach ($vectorIds as $id) {
            if (!is_string($id)) {
                throw new PCDBException('Invalid vector ID: expected string');
            }
        }

        if (empty($vectorIds)) {
            return ['message' => 'No vectors found in the namespace'];
        }

        return $this->deleteVectors($indexName, array_values($vectorIds), $namespace);
    }
}
