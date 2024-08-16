# PHP Client for Pinecone Vector Databases

This PHP Client allows to to interact with Pinecone´s vector database API, allowing you to create, manage, and query vectors and indexes. This library is designed to be easy to use and integrate into your PHP projects.

## Features

- Create, update, delete, and describe indexes.
- Upsert, fetch, query, and delete vectors.
- Manage index configurations and metadata.



## Configuration

### Environment Variables

Create a `.env` file in your project root directory to store the environment variables for the client. Rename the existing `.env.example` file to `.env` and configure it with your settings:

```bash
mv .env.example .env
```

### Example Configuration

Create a `config.php` file in your project root directory to store the configuration settings for the client. Rename the existing `examples/config.example.php` file to `config.php`:

```bash
mv examples/config.example.php examples/config.php
```

Here is an example configuration file:

```php
<?php

return [
    'api_key' => 'your_api_key_here',
    'environment' => 'your_environment_here',
    'api_version' => 'your_api_version_here',
    'custom_endpoint' => 'your_custom_endpoint_here',
    'index_name' => 'your_index_name_here',
];
```

## Usage

### Index Operations

The `Index.php` wrapper simplifies interactions with Pinecone's API for index operations. You can create, update, delete, and list indexes easily.

#### Initialize the Index Class

Initialize the `Index` class using the default configuration or a custom endpoint.

```php
use PCDB\Index;

$index = new Index();
```

#### Create an Index

To create an index, specify the metric, dimensions, and other configuration options.

```php
$indexConfig = [
    'indexName' => 'example-index',
    'metric' => 'cosine',
    'dimension' => 1536,
    'spec' => [
        'serverless' => [
            'cloud' => 'aws',
            'region' => 'us-east-1'
        ],
        'replicas' => 1,
        'shards' => 1,
    ]
];
$response = $index->createIndex($indexConfig);
```

#### Describe an Index

Get details about a specific index:

```php
$response = $index->describeIndex('example-index');
```

#### List All Indexes

To list all indexes:

```php
$response = $index->listIndexes();
```

#### Delete an Index

Delete a specific index:

```php
$response = $index->deleteIndex('example-index');
```


#### Describe Index Statistics

Retrieve statistics for a specific index:

To retrieve statistics for a specific index, you need to use a custom endpoint since the default Pinecone API endpoint doesn't handle this request.

Make sure to define a custom endpoint for your Pinecone environment and pass a Config() instance to the Index class. For all other operations, the default API endpoint can be used:

```php
use PCDB\Http\Config;
use PCDB\Index;

$config = new Config();

$customEndpoint = "your-custom-endpoint.pinecone.io";

$indexService = new Index($config, $custom_endpoint);

$response = $index->describeIndexStats('example-index');
```

### Vector Operations

The `Vector.php` wrapper simplifies vector operations, such as upserting, fetching, querying, and deleting vectors.

#### Initialize the Vector Class

You can initialize the `Vector` class using default configurations or a custom endpoint.

```php
use PCDB\Vector;
use PCDB\Http\Config;

$config = new Config();

$customEndpoint = "your-custom-endpoint.pinecone.io";

$vector = new Vector($config, $custom_endpoint);
```

#### Upsert Vectors

To upsert vectors into a specified index:

```php
use PCDB\Models\VectorModel;

$indexName = 'example-index';
$vectors = [
    new VectorModel('vec1', array_fill(0, 1536, 0.5)),
    new VectorModel('vec2', array_fill(0, 1536, 0.3))
];

$response = $vector->upsertVectors($indexName, $vectors);
```

#### Fetch Vectors

Fetch vectors by their IDs:

```php
$vectorIds = ['vec1', 'vec2'];

$response = $vector->fetchVectors($indexName, $vectorIds);
```

#### Query Vectors

Query vectors from a specified index:

```php
use PCDB\Models\VectorQuery;

$query = new VectorQuery(
    array_fill(0, 1536, 0.1),
    2,
    null,
    ['genre' => ['$eq' => 'comedy']],
    true,
    true
);

$response = $vector->queryVectors($indexName, $query);
```

#### Update Vectors

To update vector values or metadata:

```php
$vectorId = 'vec1';
$values = array_fill(0, 1536, 0.6);
$metadata = ['category' => 'example'];

$response = $vector->updateVector($indexName, $vectorId, $values, $metadata);
```

#### List Vector IDs

List all vector IDs in a specified index:

```php
$response = $vector->listVectorIDs($indexName);
```

#### Delete Vectors

To delete vectors from a specified index:

```php
$vectorIds = ['vec1', 'vec2'];
$response = $vector->deleteVectors($indexName, $vectorIds);
```

## JSON Schema Validation

All requests and responses are validated against predefined JSON schemas for better input validation and error handling. The validation ensures that the data passed to Pinecone APIs complies with the expected formats.

## Testing

The package comes with unit and integration tests using PHPUnit. Before submitting a pull request, make sure to run the tests:

```bash
./vendor/bin/phpunit
```

Also make sure to add valid Pinecone credentials to your .env. Otherwise the Integration tests will not work.

## Upcoming Features
- **Support for collections**: Advanced support for managing collections of vectors.
- **Inference features**: Enhanced vector inference capabilities.


## Contributing

Feel free to open issues or pull requests if you find bugs or want to contribute new features. Make sure to write tests for any new functionality.

## License

This project is licensed under the MIT License. See the `LICENSE` file for more information.

### Legal Disclaimer
Pinecone™ is a registered trademark of Pinecone LLC.

This project is for research purposes only and is not affiliated, endorsed, or vetted by Pinecone LLC. The pcdb-php is an open-source API client for PHP that interacts with Pinecone's APIs.

Please note, this client is not fully tested and should not be used in production systems.


----------------------


