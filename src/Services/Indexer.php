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
use Ashleyfae\LaravelElasticsearch\Traits\Indexable;
use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Model;

class Indexer
{
    /** @var Model&Indexable */
    protected Model $model;

    public function __construct(protected Client $elasticClient)
    {

    }

    /**
     * Sets the model.
     *
     * @param  Model&Indexable  $model
     *
     * @return $this
     */
    public function forModel(Model $model): static
    {
        $this->model = $model;

        return $this;
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

}
