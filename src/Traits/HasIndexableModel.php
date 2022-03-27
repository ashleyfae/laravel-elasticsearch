<?php
/**
 * HasModel.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Traits;

use Ashleyfae\LaravelElasticsearch\Exceptions\InvalidModelException;
use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Repositories\ElasticIndexRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

trait HasIndexableModel
{
    /** @var Model&Indexable */
    public Model $model;

    /** @var string Indexable model alias name (indexable_type on ElasticIndex) */
    public string $indexableType;

    /** @var ElasticIndex index record */
    public ElasticIndex $elasticIndex;

    /**
     * Validates the model to ensure it has the Indexable trait.
     *
     * @param  Model  $model
     *
     * @return void
     * @throws InvalidModelException
     */
    public function validateModel(Model $model): void
    {
        if (! is_object($model) || ! in_array(Indexable::class, class_uses($model), true)) {
            throw new InvalidModelException();
        }
    }

    /**
     * Sets the model.
     *
     * @param  Model&Indexable  $model
     *
     * @return $this
     * @throws InvalidModelException
     */
    public function setModel(Model $model): static
    {
        $this->validateModel($model);

        $this->model = $model;

        return $this;
    }

    /**
     * Sets the indexable type.
     *
     * @param  string|ElasticIndex  $indexableType
     * @param  bool  $queryElasticIndex
     *
     * @return $this
     * @throws InvalidModelException
     */
    public function forIndexableType(string|ElasticIndex $indexableType, bool $queryElasticIndex = true): static
    {
        if ($indexableType instanceof ElasticIndex) {
            $this->elasticIndex  = $indexableType;
            $this->indexableType = $this->elasticIndex->indexable_type;
        } else {
            $this->indexableType = $indexableType;

            if ($queryElasticIndex) {
                $this->elasticIndex = app(ElasticIndexRepository::class)->getIndexByIndexableType($this->indexableType);
            }
        }

        $this->setModel(new (Relation::getMorphedModel($this->indexableType)));

        return $this;
    }
}
