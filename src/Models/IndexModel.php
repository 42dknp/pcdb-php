<?php declare(strict_types=1);

namespace PCDB\Models;

/**
 * Index Model
 *
 * Represents an index in PCDB.
 */
class IndexModel
{
    public string $name;
    public string $metric;
    public int $dimension;
    public int $replicas;
    public int $shards;

    /**
     * Index constructor.
     *
     * @param string $name      Name of the index.
     * @param string $metric    Metric used for the index.
     * @param int    $dimension Dimension of the index.
     * @param int    $replicas  Number of replicas.
     * @param int    $shards    Number of shards.
     */
    public function __construct(string $name, string $metric, int $dimension, int $replicas = 1, int $shards = 1)
    {
        $this->name = $name;
        $this->metric = $metric;
        $this->dimension = $dimension;
        $this->replicas = $replicas;
        $this->shards = $shards;
    }
}
