<?php
/**
 * TestCase.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Tests;

use Ashleyfae\LaravelElasticsearch\ElasticServiceProvider;
use Ashleyfae\LaravelElasticsearch\Tests\Helpers\CanTestInaccessibleMethods;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Http\Mock\Client as MockHttpClient;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use LazilyRefreshDatabase, CanTestInaccessibleMethods;

    protected MockHttpClient $mockHttpClient;

    protected function getEnvironmentSetUp($app)
    {
        config()->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');

        $this->mockHttpClient = new MockHttpClient();

        $app->instance(
            Client::class,
            ClientBuilder::create()->setHttpClient($this->mockHttpClient)->build()
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ElasticServiceProvider::class,
        ];
    }


}
