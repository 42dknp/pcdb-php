<?php declare(strict_types=1);

namespace PCDB;

use PCDB\Http\Client;
use PCDB\Http\Config;
use PCDB\Services\IndexService;
use PCDB\Models\IndexConfig;
use PCDB\Exceptions\PCDBException;

/**
 * Index
 *
 * Simplified interface for index operations.
 */
class Index
{
    private IndexService $_indexService;
    
    /**
     * Index constructor.
     *
     * Initializes the IndexService with the provided configuration.
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
        $this->_indexService = new IndexService($client);
    }

    /**
     * Create a new index.
     *
     * @param string                    $indexName          Name of the index.
     * @param string                    $metric             Metric used for the index.
     * @param int                       $dimension          Dimension of the index.
     * @param array<string, mixed>      $spec               Specification for the index (pod-based or serverless).
     * @param array<string, mixed>|null $metadataConfig     Metadata configuration for the index.
     * @param string|null               $deletionProtection Deletion protection setting.
     * 
     * @return array<string, mixed>
     */
    public function createIndex(string $indexName, string $metric, int $dimension, array $spec, ?array $metadataConfig = null, ?string $deletionProtection = null): array
    {
        $config = new IndexConfig(
            $indexName, $metric, $dimension, $spec, $metadataConfig, $deletionProtection
        );
        return $this->_indexService->createIndex($config);
    }

    /**
     * Delete an index by its name.
     *
     * @param string $indexName Name of the index to delete.
     * 
     * @return array<string, mixed> Response from the API.
     * @throws PCDBException
     */
    public function deleteIndex(string $indexName): array
    {
        return $this->_indexService->deleteIndex($indexName);
    }

    /**
     * List all indexes.
     *
     * @return array<string, mixed> Response from the API.
     * @throws PCDBException
     */
    public function listIndexes(): array
    {
        return $this->_indexService->listIndexes();
    }

    /**
     * Describe an index by its name.
     *
     * @param string $indexName Name of the index to describe.
     * 
     * @return array<string, mixed> Response from the API.
     * @throws PCDBException
     */
    public function describeIndex(string $indexName): array
    {
        return $this->_indexService->describeIndex($indexName);
    }

    /**
     * Update the configuration of an index.
     *
     * @param string               $indexName   Name of the index to update.
     * @param array<string, mixed> $indexConfig Configuration array for the index.
     * 
     * @return array<string, mixed>
     */
    public function updateIndexConfig(string $indexName, array $indexConfig): array
    {
        $spec = $indexConfig['spec'] ?? [];
        /**
         * Ensure type declaration
         * 
         * @var array<string, mixed>|null $metadataConfig
         */
        $metadataConfig = isset($indexConfig['metadataConfig']) ? (array) $indexConfig['metadataConfig'] : null;
        $deletionProtection = isset($indexConfig['deletion_protection']) && is_string($indexConfig['deletion_protection']) ? $indexConfig['deletion_protection'] : null;

        $config = new IndexConfig(
            $indexName,
            '', // Metric is not required for update
            0,  // Dimension is not required for update
            is_array($spec) ? $spec : [],
            is_array($metadataConfig) ? $metadataConfig : null,
            $deletionProtection
        );

        return $this->_indexService->updateIndexConfig($indexName, $config);
    }

    /**
     * Describe the statistics of an index by its name.
     *
     * @param string $indexName Name of the index.
     * 
     * @return array<string, mixed> Response from the API.
     * @throws PCDBException
     */
    public function describeIndexStats(string $indexName): array
    {
        return $this->_indexService->describeIndexStats($indexName);
    }

    /**
     * Create a backup of an index.
     *
     * @param string $indexName  Name of the index to back up.
     * @param string $backupName Name of the backup.
     * 
     * @return array<string, mixed> Response from the API.
     * @throws PCDBException
     */
    public function createBackup(string $indexName, string $backupName): array
    {
        return $this->_indexService->createBackup($indexName, $backupName);
    }

    /**
     * Restore an index from a backup.
     *
     * @param string $indexName  Name of the new index to create.
     * @param int    $dimension  Dimension of the vectors.
     * @param string $metric     Metric used for the index.
     * @param string $backupName Name of the backup to restore from.
     * 
     * @return array<string, mixed> Response from the API.
     * @throws PCDBException
     */
    public function restoreFromBackup(string $indexName, int $dimension, string $metric, string $backupName): array
    {
        return $this->_indexService->restoreFromBackup($indexName, $dimension, $metric, $backupName);
    }
}
