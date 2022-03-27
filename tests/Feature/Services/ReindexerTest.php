<?php
/**
 * ReindexerTest.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Tests\Feature\Services;

use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Services\IndexManager;
use Ashleyfae\LaravelElasticsearch\Services\Reindexer;
use Ashleyfae\LaravelElasticsearch\Tests\TestCase;
use Elasticsearch\Client;
use Mockery;

/**
 * @covers \Ashleyfae\LaravelElasticsearch\Services\Reindexer
 */
class ReindexerTest extends TestCase
{
    protected ElasticIndex $elasticIndex;

    public function setUp(): void
    {
        parent::setUp();

        $this->mock(Client::class);

        $this->elasticIndex = ElasticIndex::withoutEvents(function () {
            return ElasticIndex::factory()->create([
                'indexable_type' => 'index',
                'version_number' => 1,
            ]);
        });
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\Reindexer::setIndexNames()
     */
    public function testCanSetIndexNames()
    {
        $reindexer = app(Reindexer::class)->forIndex($this->elasticIndex);

        $this->invokeProtectedMethod($reindexer, 'setIndexNames');

        $this->assertSame('index_v1', $reindexer->previousIndexName);
        $this->assertSame('index_v2', $reindexer->newIndexName);
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\Reindexer::parseMapping()
     */
    public function testCanParseMapping()
    {
        $mockedIndex = Mockery::mock(ElasticIndex::class);
        $mockedIndex->expects('getAttribute')
            ->once()
            ->with('mapping')
            ->andReturn('{"properties": {"field": "value"}}');

        $reindexer = app(Reindexer::class)->forIndex($mockedIndex);

        $this->invokeProtectedMethod($reindexer, 'parseMapping');

        $this->assertSame([
            'properties' => [
                'field' => 'value',
            ],
        ], $this->getProtectedProperty($reindexer, 'mapping'));
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\Reindexer::createNewIndex()
     */
    public function testCanCreateNewIndex()
    {
        $this->mock(IndexManager::class, function(Mockery\MockInterface $mock) {
            $mock->expects('createIndex')
                ->once()
                ->with('index_v2', ['settings' => ['refresh_interval' => 0, 'number_of_replicas' => 0]])
                ->andReturnNull();
        });

        /** @var Reindexer&Mockery\MockInterface $reindexer */
        $reindexer = app(Reindexer::class);
        $reindexer->mapping = ['settings' => ['refresh_interval' => '60s', 'number_of_replicas' => 5]];
        $reindexer->newIndexName = 'index_v2';

        $this->invokeProtectedMethod($reindexer, 'createNewIndex');
    }
}
