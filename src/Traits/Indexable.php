<?php
/**
 * Indexable.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Traits;

use Ashleyfae\LaravelElasticsearch\Services\DocumentIndexer;
use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @mixin Model
 */
trait Indexable
{

    public function getIndexer(): DocumentIndexer
    {
        /** @var DocumentIndexer $indexer */
        $indexer = app(DocumentIndexer::class);

        return $indexer->forModel(new static);
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
     */
    public function indexModel(): void
    {
        $this->getIndexer()->index();
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

}
