<?php declare(strict_types=1);

namespace PCDB\Validation;

use JsonSchema\Validator;
use PCDB\Exceptions\PCDBException;

/**
 * JsonValidator
 *
 * Validates JSON data against predefined schemas.
 */
class PCDBValidator
{
    public Validator $validator;

    public function __construct(?Validator $validator)
    {
        $this->validator = $validator ?? new Validator();
    }

    /**
     * Validates the JSON data against the specified schema.
     *
     * @param array<string, mixed> $data   The JSON data to validate.
     * @param string               $schema The schema file name.
     * 
     * @return bool True if valid, false otherwise.
     * @throws PCDBException If validation fails.
     */
    public function validate(array $data, string $schema, ?string $schemaDir = null): bool
    {
        $schemaData = $this->loadSchema($schema, $schemaDir);
        $this->performValidation($data, $schemaData);
        $this->_checkForErrors();

        return true;
    }

    /**
     * Loads the schema from the file system.
     *
     * @param string      $schema    The schema file name.
     * @param string|null $schemaDir Optional path to the schema file.
     *
     * @return object The schema data.
     * @throws PCDBException If the schema file is not found.
     */
    public function loadSchema(string $schema, ?string $schemaDir = null): object
    {
        if ($schemaDir === null) {
            $schemaPath = __DIR__ . "/Schemas/$schema";
        } else {
            $schemaPath = rtrim($schemaDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $schema;
        }
    
        $this->validateSchemaExists($schemaPath);
    
        $schemaContent = file_get_contents($schemaPath);
        if ($schemaContent === false) {
            throw new PCDBException('Failed to read schema file.');
        }
    
        $schemaData = json_decode($schemaContent);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PCDBException('Invalid JSON in schema file.');
        }
    
        if (!is_object($schemaData)) {
            throw new PCDBException('Schema should be a valid JSON object.');
        }
    
        return $schemaData;
    }
    


    /**
     * Validates that the schema file exists.
     *
     * @param string $schemaPath The full path to the schema file.
     * 
     * @throws PCDBException If the schema file does not exist.
     */
    public function validateSchemaExists(string $schemaPath): void
    {
        if (!file_exists($schemaPath)) {
            throw new PCDBException("Schema file not found: $schemaPath");
        }
    }

    /**
     * Performs the JSON validation against the loaded schema.
     *
     * @param array<string, mixed> $data       The JSON data to validate.
     * @param object               $schemaData The schema data object.
     */
    public function performValidation(array $data, object $schemaData): void
    {
        if ($data == null) {
            return;
        }

        $jsonData = json_encode($data);

        if ($jsonData === false) {
            throw new PCDBException('Failed to encode data to JSON');
        }

        $decodedData = json_decode($jsonData);

        if ($decodedData === null) {
            throw new PCDBException('Failed to decode JSON data');
        }

        $this->validator->validate($decodedData, $schemaData);
    }


    /**
     * Checks for validation errors and throws an exception if any are found.
     *
     * @throws PCDBException If the JSON data does not validate against the schema.
     */
    private function _checkForErrors(): void
    {
        if (!$this->validator->isValid()) {
            $errors = array_map(
                fn($e) => "[{$e['property']}] {$e['message']}",
                $this->validator->getErrors()
            );
            throw new PCDBException("JSON does not validate. Errors: " . implode(", ", $errors));
        }
    }

    /**
     * Checks for a non-empty value
     *
     * @param string $value     The value to check
     * @param string $fieldName The name of the field being checked
     *
     * @return void
     *
     * @throws PCDBException If the value is empty
     */
    public function checkNonEmptyValue(string $value, string $fieldName): void
    {
        if (empty($value)) {
            throw new PCDBException("$fieldName cannot be empty");
        }
    }

    /**
     * Checks for a valid Index Name
     *
     * @param object|string $config The configuration object or string
     *
     * @return void
     *
     * @throws PCDBException If the index name is empty
     */
    public function checkIndexName(object|string $config): void
    {
        $indexName = is_string($config) ? $config : $config->indexName ?? null;

        $this->checkNonEmptyValue($indexName, 'Index name');
    }

    /**
     * Checks for a valid Backup Name
     *
     * @param string $backupName The backup name to check
     *
     * @return void
     *
     * @throws PCDBException If the backup name is empty
     */
    public function checkBackupName(string $backupName): void
    {
        $this->checkNonEmptyValue($backupName, 'Backup name');
    }
}
