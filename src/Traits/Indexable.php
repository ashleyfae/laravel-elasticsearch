<?php
/**
 * Indexable.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Traits;

use Ashleyfae\LaravelElasticsearch\Contracts\ResultFormatterInterface;
use Ashleyfae\LaravelElasticsearch\Contracts\SearchInterface;
use Ashleyfae\LaravelElasticsearch\Exceptions\InvalidModelException;
use Ashleyfae\LaravelElasticsearch\Exceptions\ModelDoesNotExistException;
use Ashleyfae\LaravelElasticsearch\Services\DocumentIndexer;
use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Services\Search\QueryBuilder;
use Ashleyfae\LaravelElasticsearch\Services\Search\ResultFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @mixin Model
 */
trait Indexable
{
    /**
     * Retrieves the document indexer for this model.
     *
     * @return DocumentIndexer
     * @throws InvalidModelException
     */
    public function getDocumentIndexer(): DocumentIndexer
    {
        /** @var DocumentIndexer $indexer */
        $indexer = app(DocumentIndexer::class);

        return $indexer->forModel(new static);
    }

    /**
     * Builds a SearchQuery object for this model.
     *
     * @return SearchInterface
     * @throws InvalidModelException
     */
    public function getSearchQueryBuilder(): SearchInterface
    {
        return app(QueryBuilder::class)
            ->forModel($this)
            ->setFormatter($this->getSearchFormatter())
            ->setRouting($this->getElasticRoutingValue());
    }

    /**
     * Search formatter to use for this model.
     *
     * @return ResultFormatterInterface
     */
    protected function getSearchFormatter(): ResultFormatterInterface
    {
        return app(ResultFormatter::class);
    }

    /**
     * Retrieves the index associated with this model.
     *
     * @return ElasticIndex
     * @throws ModelNotFoundException
     */
    public function getElasticIndex(): ElasticIndex
    {
        return ElasticIndex::query()->where('indexable_type', (new static)->getMorphClass())->firstOrFail();
    }

    /**
     * Makes (does not save) a new ElasticIndex instance for this model.
     *
     * @return ElasticIndex
     */
    public static function makeElasticIndex() : ElasticIndex
    {
        return (new ElasticIndex())->setAttribute('indexable_type', (new static)->getMorphClass());
    }

    /**
     * Indexes this model.
     *
     * @return void
     * @throws ModelDoesNotExistException
     */
    public function indexModel(): void
    {
        $this->getDocumentIndexer()->index();
    }

    /**
     * Creates the array of attributes to index in Elasticsearch.
     *
     * @return array
     */
    public function toElasticIndex(): array
    {
        return $this->toArray();
    }

    /**
     * Routing value.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/guide/current/routing-value.html
     *
     * @return mixed
     */
    public function getElasticRoutingValue(): mixed
    {
        return null;
    }

}
