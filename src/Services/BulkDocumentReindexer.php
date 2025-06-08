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

    protected int $numberReindexed = 0;
    protected ?int $maxToProcess = null;

    public function __construct(protected Client $elasticClient)
    {

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

        $this->setModel(new $class);
    }

    public function setMax(int $value) : static
    {
        $this->maxToProcess = $value;

        return $this;
    }

    public function reindex(): void
    {
        $this->model->getElasticBulkReindexQuery(
            function ($models) {
                $this->reindexBatch($models);

                $this->log(sprintf('Indexed %s records.', number_format($this->numberReindexed)));

                if ($this->maxToProcess && $this->numberReindexed >= $this->maxToProcess) {
                    $this->log('Hit maximum records. Stopping.');

                    return false;
                }
            }
        );
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

        foreach ($models as $model) {
            /** @var Model&Indexable $model */
            $toReindex['body'][] = [
                'index' => array_filter([
                    '_index'  => $this->elasticIndex->write_alias,
                    '_id'     => $model->getKey(),
                    'routing' => $model->getElasticRoutingValue(),
                ]),
            ];

            $toReindex['body'][] = $model->toElasticDocArray();
            $this->numberReindexed++;
        }

        if (! empty($toReindex)) {
            $response = $this->elasticClient->bulk($toReindex);

            if (! empty($response['errors'])) {
                $this->log('Found errors:');
                $this->log(json_encode($response['items'] ?? [], JSON_PRETTY_PRINT));
            }
        }
    }
}
