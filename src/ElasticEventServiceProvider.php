<?php
/**
 * ElasticEventServiceProvider.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch;

use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Observers\ElasticIndexObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class ElasticEventServiceProvider extends EventServiceProvider
{
    public function boot()
    {
        ElasticIndex::observe(ElasticIndexObserver::class);
    }
}
