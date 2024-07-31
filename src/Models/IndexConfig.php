<?php declare(strict_types=1);

namespace PCDB\Models;

/**
 * IndexConfig Model
 *
 * Represents the configuration for an index in PCDB.
 */
class IndexConfig
{
    public string $indexName;
    public string $metric;
    public int $dimension;
    public ?string $environment;
    public ?string $podType;
    public ?int $pods;
    public ?string $cloud;
    public ?string $region;
    public ?int $replicas;
    public ?int $shards;
    /**
     * Metadata Config attribute
     *
     * @var array<string, mixed>|null 
     */
    public ?array $metadataConfig;
    public ?string $deletionProtection;

    /**
     * IndexConfig constructor.
     *
     * @param string                    $indexName          Name of the index.
     * @param string                    $metric             Metric used for the index.
     * @param int                       $dimension          Dimension of the index.
     * @param array<string, mixed>      $spec               Specification for the index (pod-based or serverless).
     * @param array<string, mixed>|null $metadataConfig     Metadata configuration for the index.
     * @param string|null               $deletionProtection Deletion protection setting.
     */
    public function __construct(string $indexName, string $metric, int $dimension, array $spec, ?array $metadataConfig = null, ?string $deletionProtection = null)
    {
        $this->indexName = $indexName;
        $this->metric = $metric;
        $this->dimension = $dimension;

        $this->environment = isset($spec['pod']) && is_array($spec['pod']) && isset($spec['pod']['environment']) && is_string($spec['pod']['environment']) ? $spec['pod']['environment'] : null;
        $this->podType = isset($spec['pod']) && is_array($spec['pod']) && isset($spec['pod']['pod_type']) && is_string($spec['pod']['pod_type']) ? $spec['pod']['pod_type'] : null;
        $this->pods = isset($spec['pod']) && is_array($spec['pod']) && isset($spec['pod']['pods']) && is_int($spec['pod']['pods']) ? $spec['pod']['pods'] : null;

        $this->cloud = isset($spec['serverless']) && is_array($spec['serverless']) && isset($spec['serverless']['cloud']) && is_string($spec['serverless']['cloud']) ? $spec['serverless']['cloud'] : null;
        $this->region = isset($spec['serverless']) && is_array($spec['serverless']) && isset($spec['serverless']['region']) && is_string($spec['serverless']['region']) ? $spec['serverless']['region'] : null;

        $this->replicas = isset($spec['replicas']) && is_int($spec['replicas']) ? $spec['replicas'] : null;
        $this->shards = isset($spec['shards']) && is_int($spec['shards']) ? $spec['shards'] : null;

        $this->metadataConfig = $metadataConfig;
        $this->deletionProtection = $deletionProtection;
    }
}
