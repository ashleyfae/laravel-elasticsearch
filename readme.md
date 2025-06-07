# Laravel Elasticsearch

Elasticsearch query builder and indexer for Laravel. 

## Setup

First run `php artisan migrate` to create the Elastic indices table.

### Config

Set up the following environment variables:

```
ELASTICSEARCH_HOSTS=localhost:9200
ELASTICSEARCH_PW=
ELASTICSEARCH_CA_CERT_PATH=
```

### Models

For any models you plan to index, ensure you have morph maps configured in your `AppServiceProvider` :

```php
Relation::enforceMorphMap([
    'user' => User::class,
]);
```

Then add the `Indexable` trait to the model(s) in question.

### Creating the mapping

To create a mapping for an index, create a file in this location:

```
`resources/elastic-indices/{morph_map_alias}.json`
```

From there it's just normal JSON as per Elasticsearch docs:

```json
{
    "settings": {
        "number_of_shards": 2,
        "number_of_replicas": 0,
        "refresh_interval": "60s"
    },
    "mappings": {
        "properties": {
            "name": {
                "type": "keyword"
            }
        }
    }
}
```

### Building the indexed document

The model data sent to Elasticsearch comes from the model's `toElasticDocArray()` method. By default this uses the model converted to an array, but it can be overwritten.

## Commands

### Healthcheck

#### `elastic:status`

Checks the status of Elasticsaerch to ensure we can connect.

### Indexes

#### `elastic:create-index {model : Model alias name.}`

Creates an index for a given model.

#### `elastic:delete-index {model : Model alias name.}`

Deletes the index of a given model.

#### `elastic:get-index {model : Model alias name.}`

Gets the index of a given model.

#### `elastic:reindex {model : Model alias name.} {--migrate} {--max=}`

Performs a re-indexing.

- Pass `--migrate` if you want to migrate to an entirely new index. This is recommended if you've adjusted the index properties.
- Pass `--max=123` if you want to stop after a certain number of records. Note that due to batching this will be rounded to the nearest batch/chunk.

### Documents

#### `elastic:index-doc {model : Model alias name.} {id : ID of the corresponding model}`

Indexes a single document.

#### `elastic:get-doc {model : Model alias name.} {id : ID of the corresponding model}`

Retrieves a single document.

#### `elastic:delete-doc {model : Model alias name.} {id : ID of the corresponding model}`

Deletes a single document.

## Query Builder

Basic usage:

