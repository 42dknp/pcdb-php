<?php declare(strict_types=1);

namespace PCDB\Models;

/**
 * VectorMetadata Model
 *
 * Represents metadata for a vector in PCDB.
 */
class VectorMetadata
{
    public string $key;
    /**
     * Value attribute
     *
     * @var mixed 
     */
    public $value;

    /**
     * VectorMetadata constructor.
     *
     * @param string $key   Key of the metadata.
     * @param mixed  $value Value of the metadata.
     */
    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
}
