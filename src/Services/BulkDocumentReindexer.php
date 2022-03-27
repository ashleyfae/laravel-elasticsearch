<?php
/**
 * BulkDocumentReindexer.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Services;

use Ashleyfae\LaravelElasticsearch\Exceptions\InvalidModelException;
use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Traits\HasConsoleLogger;
use Ashleyfae\LaravelElasticsearch\Traits\HasIndexableModel;
use Ashleyfae\LaravelElasticsearch\Traits\Indexable;
use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Traits\Conditionable;

class BulkDocumentReindexer
{
    use HasIndexableModel, HasConsoleLogger, Conditionable;

    protected ElasticIndex $elasticIndex;
    protected int $numberReindexed = 0;

    public function __construct(protected Client $elasticClient)
    {

    }

    /**
     * Sets the ElasticIndex.
     *
     * @param  string|ElasticIndex  $indexableType  Model alias name or index object.
     *
     * @return $this
     * @throws InvalidModelException
     */
    public function forIndex(string|ElasticIndex $indexableType): static
    {
        if ($indexableType instanceof ElasticIndex) {
            $this->elasticIndex = $indexableType;
        } else {
            $this->elasticIndex = ElasticIndex::query()
                ->where('indexable_type', $indexableType)
                ->firstOrFail();
        }

        $this->parseModelClass($this->elasticIndex->indexable_type);

        return $this;
    }

    /**
     * Parses the actual indexable model class based on the alias name.
     *
     * @param  string  $indexableType
     *
     * @return void
     * @throws InvalidModelException
     */
    protected function parseModelClass(string $indexableType): void
    {
        $class = Relation::getMorphedModel($indexableType);
        if (is_null($class) || ! class_exists($class)) {
            throw new \Exception('Model not found.');
        }

        $this->forModel(new $class);
    }

    public function reindex(): void
    {
        $this->model::query()
            ->chunkById(1000, function ($models) {
                $this->reindexBatch($models);

                $this->log(sprintf('Indexed %s records.', number_format($this->numberReindexed)));
            });
    }

    /**
     * Reindexes a batch of models.
     *
     * @param  Collection  $models
     *
     * @return void
     */
    protected function reindexBatch(Collection $models): void
    {
        $toReindex = [
            'body' => [],
        ];

        if ($routingValue = $models->first()->getElasticRoutingValue()) {
            $toReindex['routing'] = $routingValue;
        }

        foreach ($models as $model) {
            /** @var Model&Indexable $model */
            $toReindex['body'][] = [
                'index' => [
                    '_index' => $this->elasticIndex->write_alias,
                    '_id'    => $model->getKey(),
                ],
            ];

            $toReindex['body'][] = $model->toElasticDocArray();
            $this->numberReindexed++;
        }

        if (! empty($toReindex)) {
            $this->elasticClient->bulk($toReindex);
        }
    }
}
