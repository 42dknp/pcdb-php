<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PCDB\Validation\PCDBValidator;
use PCDB\Exceptions\PCDBException;
use JsonSchema\Validator;

class PCDBValidatorTest extends TestCase
{
    private PCDBValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PCDBValidator(new Validator());
    }

    /**
     * @dataProvider validJsonDataProvider
     */
    public function testValidJsonPassesValidation(array $jsonData, string $schema): void
    {
        $schemaDir = __DIR__ . '/data';
        $this->assertTrue($this->validator->validate($jsonData, $schema, $schemaDir));
    }

    /**
     * @dataProvider invalidJsonDataProvider
     */
    public function testInvalidJsonThrowsException(array $jsonData, string $schema, string $expectedErrorMessage): void
    {
        $this->expectException(PCDBException::class);
        $this->expectExceptionMessage($expectedErrorMessage);

        $schemaDir = __DIR__ . '/data';
        $this->validator->validate($jsonData, $schema, $schemaDir);
    }


    public function testMissingSchemaFileThrowsException(): void
    {
        $this->expectException(PCDBException::class);
        $this->expectExceptionMessage('Schema file not found: ');
        $jsonData = ['name' => 'example-index', 'dimension' => 1536, 'metric' => 'cosine'];
        $schema = 'non_existent_schema.json';

        $this->validator->validate($jsonData, $schema);
    }

    /**
     * @dataProvider nonEmptyValueProvider
     */
    public function testCheckNonEmptyValue(string $value, string $fieldName): void
    {
        $this->validator->checkNonEmptyValue($value, $fieldName);
        $this->assertTrue(true); // If no exception is thrown, the test passes
    }

    /**
     * @dataProvider emptyValueProvider
     */
    public function testCheckNonEmptyValueThrowsException(string $value, string $fieldName, string $expectedErrorMessage): void
    {
        $this->expectException(PCDBException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
        $this->validator->checkNonEmptyValue($value, $fieldName);
    }

    /**
     * @dataProvider validIndexNameProvider
     */
    public function testCheckIndexName($config): void
    {
        $this->validator->checkIndexName($config);
        $this->assertTrue(true); // If no exception is thrown, the test passes
    }

    /**
     * @dataProvider invalidIndexNameProvider
     */
    public function testCheckIndexNameThrowsException($config, string $expectedErrorMessage): void
    {
        $this->expectException(PCDBException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
        $this->validator->checkIndexName($config);
    }

    /**
     * @dataProvider validBackupNameProvider
     */
    public function testCheckBackupName(string $backupName): void
    {
        $this->validator->checkBackupName($backupName);
        $this->assertTrue(true); // If no exception is thrown, the test passes
    }

    /**
     * @dataProvider emptyBackupNameProvider
     */
    public function testCheckBackupNameThrowsException(string $value, string $expectedErrorMessage): void
    {
        $this->expectException(PCDBException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
        $this->validator->checkBackupName($value);
    }

    // Data Providers
    public function validJsonDataProvider(): array
    {
        return [
            [
                [
                    'name' => 'example-index',
                    'dimension' => 1536,
                    'metric' => 'cosine'
                ],
                'valid_schema.json'
            ]
        ];
    }

    public function invalidJsonDataProvider(): array
    {
        return [
            [
                [
                    'name' => 'example-index'  // Missing required fields
                ],
                'valid_schema.json',
                "JSON does not validate. Errors: [dimension] The property dimension is required, [metric] The property metric is required" // Adjusted expected message
            ]
        ];
    }

    public function nonEmptyValueProvider(): array
    {
        return [
            ['example-index', 'Index name'],
            ['backup-name', 'Backup name'],
        ];
    }

    public function emptyValueProvider(): array
    {
        return [
            ['', 'Index name', 'Index name cannot be empty'],
            ['', 'Backup name', 'Backup name cannot be empty'],
        ];
    }

    public function validIndexNameProvider(): array
    {
        return [
            [(object) ['indexName' => 'example-index']],
            ['example-index'],
        ];
    }

    public function invalidIndexNameProvider(): array
    {
        return [
            ['', 'Index name cannot be empty'],
            [(object) ['indexName' => ''], 'Index name cannot be empty'],
        ];
    }

    public function emptyBackupNameProvider(): array
    {
        return [
            ['', 'Backup name cannot be empty'],
        ];
    }

    public function validBackupNameProvider(): array
    {
        return [
            ['backup-name'],
        ];
    }
}
