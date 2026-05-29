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
use Elastic\Elasticsearch\ClientBuilder;
use Http\Mock\Client as MockHttpClient;
use Mockery;
use Nyholm\Psr7\Response;
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

        $indexer = $this->createPartialMock(DocumentIndexer::class, ['validateModel', 'modelCanBeIndexed']);

        $indexer->expects($this->once())
            ->method('validateModel')
            ->with($model);

        $indexer->expects($this->once())
            ->method('modelCanBeIndexed')
            ->wilLReturn(false);

        $this->expectException(ModelDoesNotExistException::class);

        $indexer->setModel($model)->index();
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\DocumentIndexer::index()
     */
    public function testCanIndex(): void
    {
        $mockHttpClient = new MockHttpClient();
        $mockHttpClient->addResponse(new Response(
            200,
            ['X-Elastic-Product' => 'Elasticsearch'],
            json_encode(['result' => 'created'])
        ));

        $client = ClientBuilder::create()->setHttpClient($mockHttpClient)->build();

        $model = Mockery::mock(IndexableModel::class);
        $model->expects('getElasticIndex')
            ->once()
            ->andReturn(
                (new ElasticIndex())->setAttribute('indexable_type', 'test_type')
            );
        $model->expects('getKey')->once()->andReturn(1);
        $model->expects('getElasticRoutingValue')->once()->andReturnNull();
        $model->expects('toElasticDocArray')->once()->andReturn(['field' => 'value']);

        /** @var DocumentIndexer&Mockery\MockInterface $indexer */
        $indexer = Mockery::mock(DocumentIndexer::class, [$client])->makePartial();
        $indexer->shouldAllowMockingProtectedMethods();
        $indexer->expects('modelCanBeIndexed')->once()->andReturn(true);
        $indexer->expects('validateModel')->once()->andReturnNull();

        $indexer->setModel($model)->index();

        $request = $mockHttpClient->getLastRequest();
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/test_type_write/_doc/1', $request->getUri()->getPath());
        $this->assertSame(['field' => 'value'], json_decode($request->getBody(), true));
    }
}
