<?php declare(strict_types=1);

namespace PCDB\Services;

use PCDB\Http\Client;
use PCDB\Exceptions\PCDBException;
use PCDB\Models\IndexConfig;
use PCDB\Validation\PCDBValidator;

/**
 * IndexService
 *
 * Provides methods to interact with PCDB indexes.
 */
class IndexService
{
    private Client $_client;
    private PCDBValidator $_validator; // Declare the property

    /**
     * IndexService constructor.
     *
     * @param Client $_client PCDB client.
     */
    public function __construct(Client $_client, ?PCDBValidator $_validator = null)
    {
        $this->_client = $_client;
        $this->_validator = $_validator ?? new PCDBValidator(new \JsonSchema\Validator());
    }

    /**
     * Creates a new index with the specified configuration.
     *
     * @param IndexConfig $config Index configuration object.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function createIndex(IndexConfig $config): array
    {
        $this->_validator->checkIndexName($config);

        $spec = [];
        
        if ($config->environment && $config->podType && $config->pods !== null) {
            $spec['pod'] = [
                'environment' => $config->environment,
                'pod_type' => $config->podType,
                'pods' => $config->pods,
            ];
        } elseif ($config->cloud && $config->region) {
            $spec['serverless'] = [
                'cloud' => $config->cloud,
                'region' => $config->region,
            ];
        } else {
            throw new PCDBException('Invalid specification for index creation');
        }

        $spec['replicas'] = $config->replicas;
        $spec['shards'] = $config->shards;

        $payload = [
            'name' => $config->indexName,
            'dimension' => $config->dimension,
            'metric' => $config->metric,
            'spec' => $spec,
            'deletion_protection' => $config->deletionProtection ?? 'disabled',
        ];

        $this->_validator->validate($payload, 'Indexes/create_index_request_schema.json');

        $response = $this->_client->request(
            'POST', "/indexes", $payload
        );

        $this->_validator->validate($response, 'Indexes/create_index_response_schema.json');

        return $response;
    }

    /**
     * Deletes the specified index.
     *
     * @param string $indexName Name of the index to delete.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function deleteIndex(string $indexName): array
    {
        $this->_validator->checkIndexName($indexName);

        $this->_validator->validate(['indexName' => $indexName], 'Indexes/base_index_request_schema.json');

        return $this->_client->request("DELETE", "/indexes/$indexName");
    }

    /**
     * Lists all indexes.
     *
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function listIndexes(): array
    {
        $response = $this->_client->request('GET', "/indexes");

        $this->_validator->validate($response, 'Indexes/list_indexes_response_schema.json');

        return $response;
    }

    /**
     * Describes the specified index.
     *
     * @param string $indexName Name of the index to describe.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function describeIndex(string $indexName): array
    {
        $this->_validator->checkIndexName($indexName);

         $this->_validator->validate(['indexName' => $indexName], 'Indexes/base_index_request_schema.json');

        $response = $this->_client->request('GET', "/indexes/$indexName");

        $this->_validator->validate($response, 'Indexes/describe_index_response_schema.json');

        return $response;
    }

    /**
     * Updates the configuration of the specified index.
     *
     * @param string      $indexName Name of the index to update.
     * @param IndexConfig $config    New configuration object.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function updateIndexConfig(string $indexName, IndexConfig $config): array
    {
        $this->_validator->checkIndexName($indexName);

        $payload = [];
        
        if ($config->environment && $config->podType && $config->pods !== null) {
            $payload['spec'] = [
                'pod' => [
                    'environment' => $config->environment,
                    'pod_type' => $config->podType,
                    'pods' => $config->pods,
                ],
                'replicas' => $config->replicas,
            ];
        } elseif ($config->cloud && $config->region) {
            // Serverless index - only update deletion protection
            $payload['deletion_protection'] = $config->deletionProtection;
        } else {
            throw new PCDBException('Invalid specification for index update');
        }

        // Add deletion protection to the payload if it exists
        if (!empty($config->deletionProtection)) {
            $payload['deletion_protection'] = $config->deletionProtection;
        }

        $this->_validator->validate($payload, 'Indexes/update_index_request_schema.json');

        $response = $this->_client->request('PATCH', "/indexes/$indexName", $payload);

        $this->_validator->validate($response, 'Indexes/update_index_response_schema.json');

        return $response;
    }

    /**
     * Retrieves the statistics of the specified index.
     *
     * @param string $indexName Name of the index.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function describeIndexStats(string $indexName): array
    {
        $this->_validator->checkIndexName($indexName);

        $this->_validator->validate(['indexName' => $indexName], 'Indexes/base_index_request_schema.json');

        $response = $this->_client->request('POST', "/describe_index_stats", ['index_name' => $indexName]);

        $this->_validator->validate($response, 'Indexes/describe_index_stats_response_schema.json');

        return $response;
    }

    /**
     * Creates a backup of the specified index.
     *
     * @param string $indexName  Name of the index to back up.
     * @param string $backupName Name of the backup.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function createBackup(string $indexName, string $backupName): array
    {
        $this->_validator->checkIndexName($indexName);
        $this->_validator->checkBackupName($backupName);

        $payload = [
            'name' => $backupName,
            'source' => $indexName
        ];
    
        $this->_validator->validate($payload, 'Indexes/create_backup_request_schema.json');
    
        $response = $this->_client->request(
            'POST', "/collections", $payload
        );
    
        $this->_validator->validate($response, 'Indexes/create_backup_response_schema.json');
    
        return $response;
    }

    /**
     * Restores an index from a backup.
     *
     * @param string $indexName  Name of the new index to create.
     * @param int    $dimension  Dimension of the vectors.
     * @param string $metric     Metric used for the index.
     * @param string $backupName Name of the backup to restore from.
     * 
     * @throws PCDBException
     * @return array<string, mixed>
     */
    public function restoreFromBackup(string $indexName, int $dimension, string $metric, string $backupName): array
    {
        $this->_validator->checkIndexName($indexName);
        $this->_validator->checkBackupName($backupName);

        $payload = [
            'name' => $indexName,
            'dimension' => $dimension,
            'metric' => $metric,
            'source_collection' => $backupName
        ];
    
        $this->_validator->validate($payload, 'Indexes/restore_from_backup_request_schema.json');
    
        $response = $this->_client->request(
            'POST', "/indexes", $payload
        );
    
        $this->_validator->validate($response, 'Indexes/restore_from_backup_response_schema.json');
    
        return $response;
    }
}
