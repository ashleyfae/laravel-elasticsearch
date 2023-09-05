<?php
/**
 * ClauseBuilderTest.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2023, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Tests\Unit\Services\Search;

use Ashleyfae\LaravelElasticsearch\Services\Search\ClauseBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ashleyfae\LaravelElasticsearch\Services\Search\ClauseBuilder
 */
class ClauseBuilderTest extends TestCase
{
    /**
     * @covers ::addHighlighting()
     * @dataProvider providerCanAddHighlighting
     *
     * @param  string[]  $fields
     * @param  string  $expectedBody
     *
     * @return void
     */
    public function testCanAddHighlighting(array $fields, string $expectedBody): void
    {
        $builder = new ClauseBuilder();
        $builder->addHighlighting($fields);

        $this->assertSame($expectedBody, json_encode($builder->getBody()));
    }

    /** @see testCanAddHighlighting */
    public function providerCanAddHighlighting(): \Generator
    {
        yield '1 field' => [
            'fields' => ['description'],
            'expectedBody' => '{"highlight":{"fields":{"description":[]}}}',
        ];
    }
}
