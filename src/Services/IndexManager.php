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
     * Creates an Elasticsearch index and the associated read/write aliases.
     *
     * @return void
     * @throws IndexAlreadyExistsException
     */
    public function createIndex(): void
    {
        if ($this->modelHasIndex()) {
            throw new IndexAlreadyExistsException();
        }

        $index                 = new ElasticIndex();
        $index->indexable_type = $this->model->getMorphClass();
        $index->version_number = 1;
        $index->save();

        // @todo move this bit to an observer
        $this->elasticClient->indices()->create([
            'index' => $index->index_name,
            'body'  => $index->mapping,
        ]);

        foreach ([$index->read_alias, $index->write_alias] as $alias) {
            if (empty($alias)) {
                continue;
            }

            $this->elasticClient->indices()->putAlias([
                'index' => $index->index_name,
                'name'  => $alias,
            ]);
        }
    }

    public function deleteIndex(): array
    {
        return $this->elasticClient->indices()->delete([
            'index' => $this->model->getElasticIndex()->index_name,
        ]);
    }
}
