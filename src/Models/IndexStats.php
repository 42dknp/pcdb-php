<?php declare(strict_types=1);

namespace PCDB\Models;

/**
 * IndexStats Model
 *
 * Represents the statistics for an index in PCDB.
 */
class IndexStats
{
    public int $totalVectors;
    public int $indexSize;

    /**
     * IndexStats constructor.
     *
     * @param int $totalVectors Total number of vectors in the index.
     * @param int $indexSize    Size of the index.
     */
    public function __construct(int $totalVectors, int $indexSize)
    {
        $this->totalVectors = $totalVectors;
        $this->indexSize = $indexSize;
    }
}
