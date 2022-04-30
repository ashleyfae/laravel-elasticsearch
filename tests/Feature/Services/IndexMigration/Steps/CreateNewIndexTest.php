<?php
/**
 * CreateNewIndexTest.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Tests\Feature\Services\IndexMigration\Steps;

use Ashleyfae\LaravelElasticsearch\Services\IndexManager;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\CreateNewIndex;
use Ashleyfae\LaravelElasticsearch\Tests\TestCase;
use Mockery\MockInterface;

class CreateNewIndexTest extends TestCase
{
    /**
     * @covers CreateNewIndex::up()
     */
    public function testCanUp()
    {
        $this->mock(IndexManager::class, function(MockInterface $mock) {
            $mock->expects('createIndex')
                ->once()
                ->with('index_v2', ['settings' => ['refresh_interval' => 0, 'number_of_replicas' => 0]])
                ->andReturnNull();
        });

        app(CreateNewIndex::class)
            ->setIndexName('index_v2')
            ->setMapping(['settings' => ['refresh_interval' => 0, 'number_of_replicas' => 0]])
            ->up();
    }
}
