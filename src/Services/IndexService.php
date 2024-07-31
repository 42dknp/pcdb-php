<?php declare(strict_types=1);

namespace PCDB\Services;

use PCDB\Http\Client;
use PCDB\Exceptions\PCDBException;
use PCDB\Models\IndexConfig;

/**
 * IndexService
 *
 * Provides methods to interact with PCDB indexes.
 */
class IndexService
{
    private Client $_client;

    /**
     * IndexService constructor.
     *
     * @param Client $_client PCDB client.
     */
    public function __construct(Client $_client)
    {
        $this->_client = $_client;
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
        if (empty($config->indexName)) {
            throw new PCDBException('Index name cannot be empty');
        }

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

        return $this->_client->request(
            'POST', "/indexes", [
            'name' => $config->indexName,
            'dimension' => $config->dimension,
            'metric' => $config->metric,
            'spec' => $spec,
            'deletion_protection' => $config->deletionProtection ?? 'disabled',
            ]
        );
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
        if (empty($indexName)) {
            throw new PCDBException('Index name cannot be empty');
        }

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
        return $this->_client->request('GET', "/indexes");
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
        if (empty($indexName)) {
            throw new PCDBException('Index name cannot be empty');
        }

        return $this->_client->request('GET', "/indexes/$indexName");
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
        if (empty($indexName)) {
            throw new PCDBException('Index name cannot be empty');
        }

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

        return $this->_client->request('PATCH', "/indexes/$indexName", $payload);
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
        if (empty($indexName)) {
            throw new PCDBException('Index name cannot be empty');
        }

        return $this->_client->request('POST', "/describe_index_stats", ['index_name' => $indexName]);
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
        if (empty($indexName)) {
            throw new PCDBException('Index name cannot be empty');
        }
        if (empty($backupName)) {
            throw new PCDBException('Backup name cannot be empty');
        }

        return $this->_client->request(
            'POST', "/collections", [
            'name' => $backupName,
            'source' => $indexName
            ]
        );
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
        if (empty($indexName)) {
            throw new PCDBException('Index name cannot be empty');
        }
        if (empty($backupName)) {
            throw new PCDBException('Backup name cannot be empty');
        }

        return $this->_client->request(
            'POST', "/indexes", [
            'name' => $indexName,
            'dimension' => $dimension,
            'metric' => $metric,
            'source_collection' => $backupName
            ]
        );
    }
}
