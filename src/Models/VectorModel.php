<?php declare(strict_types=1);

namespace PCDB\Models;

/**
 * Vector Model
 *
 * Represents a vector in PCDB.
 */
class VectorModel
{
    public string $id;
    /**
     * Values attribute
     *
     * @var array<int, float> 
     */
    public array $values;
    /**
     * Sparse Values attribute
     *
     * @var array<string, mixed>|null 
     */
    public ?array $sparseValues;
    /**
     * Metadata attribute
     *
     * @var array<string, mixed>|null 
     */
    public ?array $metadata;

    /**
     * Vector constructor.
     *
     * @param string                    $id           ID of the vector.
     * @param array<int, float>         $values       Values of the vector.
     * @param array<string, mixed>|null $sparseValues Sparse values of the vector.
     * @param array<string, mixed>|null $metadata     Metadata of the vector.
     */
    public function __construct(string $id, array $values, ?array $sparseValues = null, ?array $metadata = null)
    {
        $this->id = $id;
        $this->values = $values;
        $this->sparseValues = $sparseValues;
        $this->metadata = $metadata;
    }
}
