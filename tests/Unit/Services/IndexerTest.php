<?php
/**
 * IndexerTest.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Tests\Unit\Services;

use Ashleyfae\LaravelElasticsearch\Exceptions\ModelDoesNotExistException;
use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Services\Indexer;
use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ashleyfae\LaravelElasticsearch\Services\Indexer
 */
class IndexerTest extends TestCase
{
    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\Indexer::index()
     */
    public function testCanIndexWhenModelDoesNotExist(): void
    {
        $model  = Mockery::mock(Model::class);
        $client = Mockery::mock(Client::class);

        $client->expects('index')->never();
        $model->expects('getElasticIndex')->never();

        $indexer = Mockery::mock(Indexer::class, [$client])->makePartial();
        $indexer->shouldAllowMockingProtectedMethods();
        $indexer->expects('modelCanBeIndexed')
            ->once()
            ->andReturn(false);

        $this->expectException(ModelDoesNotExistException::class);

        $indexer->forModel($model)->index();
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\Indexer::index()
     */
    public function testCanIndex(): void
    {
        $model  = Mockery::mock(Model::class);
        $client = Mockery::mock(Client::class);

        $client->expects('index')->once()->with([
            'index' => 'test_type_write',
            'id'    => 1,
            'body'  => ['data'],
        ]);

        $model->expects('getElasticIndex')
            ->once()
            ->andReturn(
                (new ElasticIndex())->setAttribute('indexable_type', 'test_type')
            );

        $model->expects('getKey')->once()->andReturn(1);

        $model->expects('toElasticIndex')->once()->andReturn(['data']);

        $indexer = Mockery::mock(Indexer::class, [$client])->makePartial();
        $indexer->shouldAllowMockingProtectedMethods();
        $indexer->expects('modelCanBeIndexed')
            ->once()
            ->andReturn(true);

        $indexer->forModel($model)->index();

        $this->expectNotToPerformAssertions();
    }
}
