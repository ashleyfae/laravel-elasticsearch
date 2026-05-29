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
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Http\Promise\Promise;

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
     * @throws MissingParameterException|NoNodeAvailableException|ClientResponseException|ServerResponseException
     */
    public function index(): void
    {
        if (! $this->modelCanBeIndexed()) {
            throw new ModelDoesNotExistException();
        }

        $args = [
            'index' => $this->model->getElasticIndex()->write_alias,
            'id'    => $this->model->getKey(),
            'body'  => $this->model->toElasticDocArray(),
        ];

        if ($routing = $this->model->getElasticRoutingValue()) {
            $args['routing'] = $routing;
        }

        $this->elasticClient->index($args);
    }

    /**
     * Gets the document.
     *
     * @throws MissingParameterException|NoNodeAvailableException|ClientResponseException|ServerResponseException
     */
    public function get() : Elasticsearch|Promise
    {
        $args = [
            'index' => $this->model->getElasticIndex()->write_alias,
            'id'    => $this->model->getKey(),
        ];

        if ($routing = $this->model->getElasticRoutingValue()) {
            $args['routing'] = $routing;
        }

        return $this->elasticClient->get($args);
    }

    /**
     * Deletes the model.
     *
     * @throws MissingParameterException|NoNodeAvailableException|ClientResponseException|ServerResponseException
     */
    public function delete(): void
    {
        $args = [
            'index' => $this->model->getElasticIndex()->write_alias,
            'id'    => $this->model->getKey(),
        ];

        if ($routing = $this->model->getElasticRoutingValue()) {
            $args['routing'] = $routing;
        }

        $this->elasticClient->delete($args);
    }
}
