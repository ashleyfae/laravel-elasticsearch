<?php
/**
 * ReindexerTest.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Tests\Feature\Services\IndexMigration;

use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Services\IndexManager;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\IndexMigrator;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\CreateNewIndex;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\SwapAlias;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\UpdateModelVersion;
use Ashleyfae\LaravelElasticsearch\Tests\TestCase;
use Elasticsearch\Client;
use Mockery;

/**
 * @covers \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\IndexMigrator
 */
class IndexMigratorTest extends TestCase
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
     * @covers \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\IndexMigrator::setIndexNames()
     */
    public function testCanSetIndexNames()
    {
        $reindexer = app(IndexMigrator::class)->forIndex($this->elasticIndex);

        $this->invokeInaccessibleMethod($reindexer, 'setIndexNames');

        $this->assertSame('index_v1', $reindexer->previousIndexName);
        $this->assertSame('index_v2', $reindexer->newIndexName);
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\IndexMigrator::parseMapping()
     */
    public function testCanParseMapping()
    {
        $mockedIndex = Mockery::mock(ElasticIndex::class);
        $mockedIndex->expects('getAttribute')
            ->once()
            ->with('mapping')
            ->andReturn('{"properties": {"field": "value"}}');

        $reindexer = app(IndexMigrator::class)->forIndex($mockedIndex);

        $this->invokeInaccessibleMethod($reindexer, 'parseMapping');

        $this->assertSame([
            'properties' => [
                'field' => 'value',
            ],
        ], $this->getInaccessibleProperty($reindexer, 'mapping'));
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\IndexMigrator::createNewIndex()
     */
    public function testCanCreateNewIndex()
    {
        $this->mock(CreateNewIndex::class, function (Mockery\MockInterface $mock) {
            $mock->expects('setMapping')
                ->once()
                ->with(['settings' => ['refresh_interval' => 0, 'number_of_replicas' => 0]])
                ->andReturnSelf();

            $mock->expects('setIndexName')
                ->once()
                ->with('index_v2')
                ->andReturnSelf();

            $mock->expects('up')
                ->once()
                ->andReturnSelf();
        });

        $reindexer               = app(IndexMigrator::class);
        $reindexer->mapping      = ['settings' => ['refresh_interval' => '60s', 'number_of_replicas' => 5]];
        $reindexer->newIndexName = 'index_v2';

        $this->invokeInaccessibleMethod($reindexer, 'createNewIndex');
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\IndexMigrator::updateWriteAlias()
     */
    public function testCanUpdateWriteAlias()
    {
        $this->mock(SwapAlias::class, function (Mockery\MockInterface $mock) {
            $mock->expects('setAliasName')
                ->once()
                ->with('index_write')
                ->andReturnSelf();

            $mock->expects('setPreviousIndexName')
                ->once()
                ->with('index_v1')
                ->andReturnSelf();

            $mock->expects('setNewIndexName')
                ->once()
                ->with('index_v2')
                ->andReturnSelf();

            $mock->expects('up')
                ->once()
                ->andReturnSelf();
        });

        $reindexer = app(IndexMigrator::class);
        $reindexer->forIndex($this->elasticIndex);
        $reindexer->previousIndexName = 'index_v1';
        $reindexer->newIndexName      = 'index_v2';

        $this->invokeInaccessibleMethod($reindexer, 'updateWriteAlias');
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\IndexMigrator::updateNewIndexSettings()
     */
    public function testCanUpdateNewIndexSettings()
    {
        $this->mock(IndexManager::class, function (Mockery\MockInterface $mock) {
            $mock->expects('updateIndexSettings')
                ->once()
                ->with('index_v2', [
                    'refresh_interval'   => '60s',
                    'number_of_replicas' => 2,
                ])
                ->andReturnNull();
        });

        $reindexer                          = app(IndexMigrator::class);
        $reindexer->newIndexName            = 'index_v2';
        $reindexer->originalRefreshInterval = '60s';
        $reindexer->originalReplicas        = 2;

        $this->invokeInaccessibleMethod($reindexer, 'updateNewIndexSettings');
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\IndexMigrator::updateReadAlias()
     */
    public function testCanUpdateReadAlias()
    {
        $this->mock(SwapAlias::class, function (Mockery\MockInterface $mock) {
            $mock->expects('setAliasName')
                ->once()
                ->with('index_read')
                ->andReturnSelf();

            $mock->expects('setPreviousIndexName')
                ->once()
                ->with('index_v1')
                ->andReturnSelf();

            $mock->expects('setNewIndexName')
                ->once()
                ->with('index_v2')
                ->andReturnSelf();

            $mock->expects('up')
                ->once()
                ->andReturnSelf();
        });

        $reindexer = app(IndexMigrator::class);
        $reindexer->forIndex($this->elasticIndex);
        $reindexer->previousIndexName = 'index_v1';
        $reindexer->newIndexName      = 'index_v2';

        $this->invokeInaccessibleMethod($reindexer, 'updateReadAlias');
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\IndexMigrator::updateModel()
     */
    public function testCanUpdateModel()
    {
        $this->mock(UpdateModelVersion::class, function (Mockery\MockInterface $mock) {
            $mock->expects('setElasticIndex')
                ->once()
                ->with($this->elasticIndex)
                ->andReturnSelf();

            $mock->expects('setNewVersion')
                ->once()
                ->with(2)
                ->andReturnSelf();

            $mock->expects('setPreviousVersion')
                ->once()
                ->with(1)
                ->andReturnSelf();

            $mock->expects('up')
                ->once()
                ->andReturnSelf();
        });

        $reindexer = app(IndexMigrator::class);
        $reindexer->forIndex($this->elasticIndex);

        $this->setInaccessibleProperty($reindexer, 'newIndexVersion', 2);

        $this->invokeInaccessibleMethod($reindexer, 'updateModel');
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\IndexMigrator::deleteOldIndex()
     */
    public function testCanDeleteOldIndex()
    {
        $this->mock(IndexManager::class, function (Mockery\MockInterface $mock) {
            $mock->expects('deleteIndex')
                ->once()
                ->with('index_v1')
                ->andReturnNull();
        });

        $reindexer                    = app(IndexMigrator::class);
        $reindexer->previousIndexName = 'index_v1';

        $this->invokeInaccessibleMethod($reindexer, 'deleteOldIndex');
    }
}
