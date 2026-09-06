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
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Http\Promise\Promise;
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
     * @see ElasticIndexObserver::created()
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

    /**
     * @throws MissingParameterException|NoNodeAvailableException|ClientResponseException|ServerResponseException
     */
    public function getIndex(string $indexName): Elasticsearch|Promise
    {
        return $this->elasticClient->indices()->get([
            'index' => $indexName,
        ]);
    }

    /**
     * @throws MissingParameterException|NoNodeAvailableException|ClientResponseException|ServerResponseException
     */
    public function createIndex(string $indexName, array $mapping): void
    {
        $this->elasticClient->indices()->create([
            'index' => $indexName,
            'body'  => $mapping,
        ]);
    }

    /**
     * @throws MissingParameterException|NoNodeAvailableException|ClientResponseException|ServerResponseException
     */
    public function addAlias(string $indexName, string $alias): void
    {
        $this->elasticClient->indices()->putAlias([
            'index' => $indexName,
            'name'  => $alias,
        ]);
    }

    /**
     * @throws MissingParameterException|NoNodeAvailableException|ClientResponseException|ServerResponseException
     */
    public function deleteIndex(string $indexName): void
    {
        $this->elasticClient->indices()->delete([
            'index' => $indexName,
        ]);
    }

    /**
     * Simultaneously removes an alias from one index and adds it to another.
     *
     * @throws MissingParameterException|NoNodeAvailableException|ClientResponseException|ServerResponseException
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

    /**
     * @throws MissingParameterException|NoNodeAvailableException|ClientResponseException|ServerResponseException
     */
    public function updateIndexSettings(string $indexName, array $body): void
    {
        $this->elasticClient->indices()->putSettings([
            'index' => $indexName,
            'body'  => $body,
        ]);
    }
}
