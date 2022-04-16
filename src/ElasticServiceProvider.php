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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class ElasticServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            $builder = ClientBuilder::create()
                ->setHosts(Config::get('elasticsearch.hosts') ?: null);

            if ($pw = Config::get('elasticsearch.basicAuthPw')) {
                $builder->setBasicAuthentication('elastic', $pw);
            }

            if ($cert = Config::get('elasticsearch.caCertPath')) {
                $builder->setSSLCert($cert);
            }

            return $builder->build();
        });
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
