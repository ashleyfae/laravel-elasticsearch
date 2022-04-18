<?php
/**
 * ElasticIndexObserver.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Observers;

use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Services\IndexManager;

class ElasticIndexObserver
{
    public function __construct(protected IndexManager $indexManager)
    {

    }

    public function created(ElasticIndex $index): void
    {
        $this->indexManager->createIndex($index->index_name, json_decode($index->mapping, true));

        foreach ([$index->read_alias, $index->write_alias] as $alias) {
            if (empty($alias)) {
                continue;
            }

            $this->indexManager->addAlias($index->index_name, $alias);
        }
    }

    /**
     * Deletes the index when the model is deleted.
     *
     * @param  ElasticIndex  $index
     *
     * @return void
     */
    public function deleted(ElasticIndex $index)
    {
        $this->indexManager->deleteIndex($index->index_name);
    }
}
