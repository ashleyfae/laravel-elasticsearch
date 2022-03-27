<?php
/**
 * IndexManager.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Services;

use Ashleyfae\LaravelElasticsearch\Exceptions\IndexAlreadyExistsException;
use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Observers\ElasticIndexObserver;
use Ashleyfae\LaravelElasticsearch\Traits\HasIndexableModel;
use Elasticsearch\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class IndexManager
{
    use HasIndexableModel;

    public function __construct(protected Client $elasticClient)
    {

    }

    /**
     * Determines if an index already exists for the model. This doesn't actually check in Elasticsearch (@todo maybe?)
     * it just checks to see if the DB record exists and assumes.
     *
     * @return bool
     */
    public function modelHasIndex(): bool
    {
        try {
            return $this->model->getElasticIndex() instanceof ElasticIndex;
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    /**
     * Creates an Elasticsearch index.
     *
     * @see ElasticIndexObserver::saved()
     *
     * @throws IndexAlreadyExistsException
     */
    public function createIndexModel(): ElasticIndex
    {
        if ($this->modelHasIndex()) {
            throw new IndexAlreadyExistsException();
        }

        $index                 = new ElasticIndex();
        $index->indexable_type = $this->model->getMorphClass();
        $index->version_number = 1;
        $index->save();

        return $index;
    }

    public function createIndex(string $indexName, array $mapping): void
    {
        $this->elasticClient->indices()->create([
            'index' => $indexName,
            'body'  => $mapping,
        ]);
    }

    public function addAlias(string $indexName, string $alias): void
    {
        $this->elasticClient->indices()->putAlias([
            'index' => $indexName,
            'name'  => $alias,
        ]);
    }

    public function deleteIndex(string $indexName): void
    {
        $this->elasticClient->indices()->delete([
            'index' => $indexName,
        ]);
    }

    /**
     * Simultaneously removes an alias from one index and adds it to another.
     *
     * @param  string  $alias
     * @param  string  $removeAliasFrom
     * @param  string  $addAliasTo
     *
     * @return void
     */
    public function swapAlias(string $alias, string $removeAliasFrom, string $addAliasTo): void
    {
        $this->elasticClient->indices()->updateAliases([
            'body' => [
                'actions' => [
                    [
                        'remove' => [
                            'index' => $removeAliasFrom,
                            'alias' => $alias,
                        ],
                    ],
                    [
                        'add' => [
                            'index' => $addAliasTo,
                            'alias' => $alias,
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function updateIndexSettings(string $indexName, array $body): void
    {
        $this->elasticClient->indices()->putSettings([
            'index' => $indexName,
            'body'  => $body,
        ]);
    }
}
