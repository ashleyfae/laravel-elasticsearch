<?php
/**
 * Indexer.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Services;

use Ashleyfae\LaravelElasticsearch\Exceptions\ModelDoesNotExistException;
use Ashleyfae\LaravelElasticsearch\Traits\HasIndexableModel;
use Elasticsearch\Client;

class DocumentIndexer
{
    use HasIndexableModel;

    public function __construct(protected Client $elasticClient)
    {

    }

    /**
     * If we are capable of indexing the model.
     *
     * @return bool
     */
    protected function modelCanBeIndexed(): bool
    {
        return $this->model->exists && $this->model->getKey();
    }

    /**
     * Indexes the current model.
     *
     * @throws ModelDoesNotExistException
     */
    public function index(): void
    {
        if (! $this->modelCanBeIndexed()) {
            throw new ModelDoesNotExistException();
        }

        $this->elasticClient->index([
            'index' => $this->model->getElasticIndex()->write_alias,
            'id'    => $this->model->getKey(),
            'body'  => $this->model->toElasticIndex(),
        ]);
    }

    /**
     * Deletes the model.
     *
     * @return void
     */
    public function delete(): void
    {
        $this->elasticClient->delete([
            'index' => $this->model->getElasticIndex()->write_alias,
            'id'    => $this->model->getKey(),
        ]);
    }
}
