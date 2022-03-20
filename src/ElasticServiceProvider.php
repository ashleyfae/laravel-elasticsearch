<?php
/**
 * ServiceProvider.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class ElasticServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            return ClientBuilder::create()
                ->setHosts($app['config']->get('elasticsearch.hosts'))
                ->build();
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/elasticsearch.php',
            'elasticsearch'
        );

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/elasticsearch.php' => config_path('elasticsearch.php')
            ], 'config');
        }
    }
}
