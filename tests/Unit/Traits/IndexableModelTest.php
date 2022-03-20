<?php
/**
 * IndexableModelTest.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Tests\Unit\Traits;

use Ashleyfae\LaravelElasticsearch\Exceptions\InvalidModelException;
use Ashleyfae\LaravelElasticsearch\Tests\Models\IndexableModel;
use Ashleyfae\LaravelElasticsearch\Traits\HasIndexableModel;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ashleyfae\LaravelElasticsearch\Tests\Models\IndexableModel
 */
class IndexableModelTest extends TestCase
{
    /**
     * @dataProvider providerCanValidateModel
     */
    public function testCanValidateModel($model, bool $validationShouldPass): void
    {
        /** @var HasIndexableModel $mock */
        $mock = $this->getMockForTrait(HasIndexableModel::class);

        if ($validationShouldPass) {
            $this->expectNotToPerformAssertions();
        } else {
            $this->expectException(InvalidModelException::class);
        }

        $mock->validateModel($model);
    }

    /** @see testCanValidateModel */
    public function providerCanValidateModel(): \Generator
    {
        yield 'Has Indexable trait' => [new IndexableModel(), true];

        yield 'Does not have Indexable trait' => [Mockery::mock(Model::class), false];
    }
}
