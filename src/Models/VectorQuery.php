<?php declare(strict_types=1);

namespace PCDB\Models;

/**
 * VectorQuery Model
 *
 * Represents a query for vectors in PCDB.
 */
class VectorQuery
{
    /**
     * Vector attribute
     *
     * @var array<int, float> 
     */
    public array $vector;
    public int $topK;
    public ?string $namespace;
    /**
     * Filter attribute
     *
     * @var array<string, mixed>|null 
     */
    public ?array $filter;
    public ?bool $includeValues;
    public ?bool $includeMetadata;

    /**
     * VectorQuery constructor.
     *
     * @param array<int, float>         $vector          Query vector.
     * @param int                       $topK            Number of top results to retrieve.
     * @param string|null               $namespace       Namespace for the query.
     * @param array<string, mixed>|null $filter          Filter for the query.
     * @param bool|null                 $includeValues   Whether to include vector values in the response.
     * @param bool|null                 $includeMetadata Whether to include metadata in the response.
     */
    public function __construct(array $vector,int $topK = 10, ?string $namespace = null, ?array $filter = null, ?bool $includeValues = false, ?bool $includeMetadata = false)
    {
        $this->vector = $vector;
        $this->topK = $topK;
        $this->namespace = $namespace;
        $this->filter = $filter;
        $this->includeValues = $includeValues;
        $this->includeMetadata = $includeMetadata;
    }
}
