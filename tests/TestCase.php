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
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use LazilyRefreshDatabase, CanTestInaccessibleMethods;

    protected function getEnvironmentSetUp($app)
    {
        config()->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    protected function getPackageProviders($app)
    {
        return [
            ElasticServiceProvider::class,
        ];
    }


}
