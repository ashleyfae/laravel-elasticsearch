<?php
/**
 * ElasticIndexObserverTest.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Tests\Feature\Observers;

use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Services\IndexManager;
use Ashleyfae\LaravelElasticsearch\Tests\TestCase;
use Elasticsearch\Client;
use Mockery\MockInterface;

class ElasticIndexObserverTest extends TestCase
{
    /**
     * @todo How to better mock this mapping.... sigh...
     * @covers \Ashleyfae\LaravelElasticsearch\Observers\ElasticIndexObserver::saved()
     * @return void
     */
    public function testCreatingIndex()
    {
        $this->markTestIncomplete();
        /*$this->mock(Client::class);
        $this->mock(IndexManager::class, function (MockInterface $mock) {
            $mock->expects('createIndex')
                ->once()
                ->with('editions_v1')
                ->andReturnNull();
        });

        $index = $this->partialMock(ElasticIndex::class, function(MockInterface $mock) {
            $mock->expects('getAttribute')
                ->once()
                ->with('mapping')
                ->andReturn('{"properties": {"field": "value"}}');
        });

        $index->indexable_type = 'editions';
        $index->version_number = 1;
        $index->save();*/
    }
}
