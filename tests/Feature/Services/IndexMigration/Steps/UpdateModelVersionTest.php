<?php
/**
 * UpdateModelVersionTest.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Tests\Feature\Services\IndexMigration\Steps;

use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\UpdateModelVersion;
use Ashleyfae\LaravelElasticsearch\Tests\TestCase;

/**
 * @coversDefaultClass \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\UpdateModelVersion
 */
class UpdateModelVersionTest extends TestCase
{
    protected ElasticIndex $elasticIndex;
    protected UpdateModelVersion $updateModelVersion;

    public function setUp(): void
    {
        parent::setUp();

        $this->elasticIndex = ElasticIndex::withoutEvents(function () {
            return ElasticIndex::factory()->create([
                'indexable_type' => 'index',
                'version_number' => 2,
            ]);
        });

        $this->updateModelVersion = app(UpdateModelVersion::class)
            ->setElasticIndex($this->elasticIndex)
            ->setPreviousVersion(2)
            ->setNewVersion(3);
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\UpdateModelVersion::up()
     */
    public function testCanUp()
    {
        $this->assertSame(2, $this->elasticIndex->version_number);

        $this->updateModelVersion->up();

        $this->assertSame(3, $this->elasticIndex->version_number);
    }

    /**
     * @covers \Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\UpdateModelVersion::down()
     */
    public function testCanDown()
    {
        $this->updateModelVersion->up();

        $this->assertSame(3, $this->elasticIndex->version_number);

        $this->updateModelVersion->down();

        $this->assertSame(2, $this->elasticIndex->version_number);
    }
}
