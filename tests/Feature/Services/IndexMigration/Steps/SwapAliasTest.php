<?php
/**
 * SwapAliasTest.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Tests\Feature\Services\IndexMigration\Steps;

use Ashleyfae\LaravelElasticsearch\Services\IndexManager;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\SwapAlias;
use Ashleyfae\LaravelElasticsearch\Tests\TestCase;
use Mockery\MockInterface;

/**
 * @coversDefaultClass \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\SwapAlias
 */
class SwapAliasTest extends TestCase
{
    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\SwapAlias::up()
     */
    public function testCanUp()
    {
        $this->mock(IndexManager::class, function (MockInterface $mock) {
            $mock->expects('swapAlias')
                ->once()
                ->with('index_write', 'index_v1', 'index_v2')
                ->andReturnNull();
        });

        app(SwapAlias::class)
            ->setAliasName('index_write')
            ->setPreviousIndexName('index_v1')
            ->setNewIndexName('index_v2')
            ->up();
    }
}
