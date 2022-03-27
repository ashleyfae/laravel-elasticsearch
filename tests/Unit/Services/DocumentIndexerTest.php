<?php
/**
 * DocumentIndexerTest.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Tests\Unit\Services;

use Ashleyfae\LaravelElasticsearch\Exceptions\ModelDoesNotExistException;
use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Services\DocumentIndexer;
use Ashleyfae\LaravelElasticsearch\Tests\Models\IndexableModel;
use Elasticsearch\Client;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ashleyfae\LaravelElasticsearch\Services\DocumentIndexer
 */
class DocumentIndexerTest extends TestCase
{
    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\DocumentIndexer::index()
     */
    public function testCanIndexWhenModelDoesNotExist(): void
    {
        $model  = Mockery::mock(IndexableModel::class);
        $client = Mockery::mock(Client::class);

        $client->expects('index')->never();
        $model->expects('getElasticIndex')->never();

        /** @var DocumentIndexer&Mockery\MockInterface $indexer */
        $indexer = Mockery::mock(DocumentIndexer::class, [$client])->makePartial();
        $indexer->shouldAllowMockingProtectedMethods();
        $indexer->expects('modelCanBeIndexed')
            ->once()
            ->andReturn(false);
        $indexer->expects('validateModel')->once()->andReturnNull();

        $this->expectException(ModelDoesNotExistException::class);

        $indexer->setModel($model)->index();
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\DocumentIndexer::index()
     */
    public function testCanIndex(): void
    {
        $model  = Mockery::mock(IndexableModel::class);
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

        $model->expects('toElasticDocArray')->once()->andReturn(['data']);

        /** @var DocumentIndexer&Mockery\MockInterface $indexer */
        $indexer = Mockery::mock(DocumentIndexer::class, [$client])->makePartial();
        $indexer->shouldAllowMockingProtectedMethods();
        $indexer->expects('modelCanBeIndexed')
            ->once()
            ->andReturn(true);
        $indexer->expects('validateModel')->once()->andReturnNull();

        $indexer->setModel($model)->index();

        $this->expectNotToPerformAssertions();
    }
}
