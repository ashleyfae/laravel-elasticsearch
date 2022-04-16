<?php
/**
 * ServiceProvider.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch;

use Ashleyfae\LaravelElasticsearch\Console\Commands\CheckStatus;
use Ashleyfae\LaravelElasticsearch\Console\Commands\CreateIndex;
use Ashleyfae\LaravelElasticsearch\Console\Commands\DeleteIndex;
use Ashleyfae\LaravelElasticsearch\Console\Commands\GetIndex;
use Ashleyfae\LaravelElasticsearch\Console\Commands\Reindex;
use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Observers\ElasticIndexObserver;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class ElasticServiceProvider extends ServiceProvider
{
    public function register()
    {
        if ($this->app['config']->get('elasticsearch.hosts')) {
            $this->app->singleton(Client::class, function ($app) {
                return ClientBuilder::create()
                    ->setHosts($app['config']->get('elasticsearch.hosts'))
                    ->build();
            });
        }
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerCommands();

        $this->mergeConfigFrom(
            __DIR__.'/../config/elasticsearch.php',
            'elasticsearch'
        );

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/elasticsearch.php' => config_path('elasticsearch.php')
            ], 'config');
        }

        ElasticIndex::observe(ElasticIndexObserver::class);
    }

    /**
     * Registers console commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckStatus::class,
                CreateIndex::class,
                DeleteIndex::class,
                GetIndex::class,
                Reindex::class,
            ]);
        }

    }
}
