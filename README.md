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

### Index

The `Index.php` wrapper class simplifies interactions with the Pinecone vector database API. Below are examples of how to use this class for various index operations.

#### Initialize the Index Class

To start using the `Index` class, you need to initialize it with the required configuration. You can use the default configuration or provide a custom endpoint.

```php
use PCDB\Index;
use PCDB\Http\Config;

$config = new Config();
$customEndpoint = 'https://custom-endpoint.svc.region.pinecone.io';
$index = new Index($config, $customEndpoint);
```
Of course you need to create an index before you get the endpoint url.

#### Create Index

To create a new index with the specified configuration:

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

#### Describe Index

To get details about a specific index:

```php
$response = $index->describeIndex('example-index');
```

#### List Indexes

To list all indexes:

```php
$response = $index->listIndexes();
```

#### Delete Index

To delete a specific index:

```php
$response = $index->deleteIndex('example-index');
```

#### Update Index Configuration

To update the configuration of an existing index:

```php
$indexConfig = [
    'metric' => 'cosine',
    'dimension' => 1536,
    'spec' => [
        'serverless' => [
            'cloud' => 'aws',
            'region' => 'us-east-1'
        ],
        'replicas' => 2,
        'shards' => 1,
    ]
];
$response = $index->updateIndexConfig('example-index', $indexConfig);
```

#### Describe Index Statistics

To get statistics about a specific index:

```php
$response = $index->describeIndexStats('example-index');
```



### Vector

The `Vector.php` wrapper class simplifies interactions with the Pinecone vector database API. Below are examples of how to use this class for various vector operations.

#### Initialize the Vector Class

To start using the `Vector` class, you need to initialize it with the required configuration. You can use the default configuration or provide a custom endpoint.

```php
use PCDB\Vector;
use PCDB\Http\Config;

$config = new Config();
$vector = new Vector($config);
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

To fetch vectors by their IDs from a specified index:

```php
$vectorIds = ['vec1', 'vec2'];
$response = $vector->fetchVectors($indexName, $vectorIds);
```

#### Query Vectors

To query vectors in a specified index:

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

To update vectors in a specified index:

```php
$vectorId = 'vec1';
$values = array_fill(0, 1536, 0.6);
$metadata = ['category' => 'example'];
$response = $vector->updateVector($indexName, $vectorId, $values, $metadata);
```

#### List Vector IDs

To list vector IDs in a specified index:

```php
$response = $vector->listVectorIDs($indexName);
```

#### Delete Vectors

To delete vectors from a specified index by their IDs:

```php
$vectorIds = ['vec1', 'vec2'];
$response = $vector->deleteVectors($indexName, $vectorIds);
```

## Upcoming Features
- Support for Collections and Inference


## Contributing

Feel free to open issues or pull requests if you find bugs or want to contribute new features. Make sure to write tests for any new functionality.

## License

This project is licensed under the MIT License. See the `LICENSE` file for more information.

### Legal Disclaimer
Pinecone™ is a registered trademark of Pinecone LLC.

This project is for research purposes only and is not affiliated, endorsed, or vetted by Pinecone LLC. The pcdb-php is an open-source API client for PHP that interacts with Pinecone's APIs.

Please note, this client is not fully tested and should not be used in production systems.


----------------------


