<?php
/**
 * IndexableObserver.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Observers;

use Ashleyfae\LaravelElasticsearch\Jobs\DeleteModel;
use Ashleyfae\LaravelElasticsearch\Jobs\IndexModel;
use Ashleyfae\LaravelElasticsearch\Traits\Indexable;
use Illuminate\Database\Eloquent\Model;

class IndexableObserver
{
    /**
     * @param  Model&Indexable  $model
     *
     * @return void
     */
    public function saved(Model $model)
    {
        IndexModel::dispatch($model);
    }

    public function deleted(Model $model)
    {
        DeleteModel::dispatch($model);
    }
}
