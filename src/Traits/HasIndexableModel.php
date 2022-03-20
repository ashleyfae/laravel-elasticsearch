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
use Illuminate\Database\Eloquent\Model;

trait HasIndexableModel
{
    /** @var Model&Indexable */
    public Model $model;

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
        if (! in_array(Indexable::class, class_uses($model), true)) {
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
    public function forModel(Model $model): static
    {
        $this->validateModel($model);

        $this->model = $model;

        return $this;
    }
}
