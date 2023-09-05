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
use Ashleyfae\LaravelElasticsearch\Tests\Helpers\CanTestInaccessibleMethods;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ashleyfae\LaravelElasticsearch\Services\Search\ClauseBuilder
 */
class ClauseBuilderTest extends TestCase
{
    use CanTestInaccessibleMethods;

    /**
     * @covers ::addHighlighting()
     * @dataProvider providerCanAddHighlighting
     *
     * @param  string[]  $fields
     * @param array|null $existingBody
     * @param  string  $expectedBody
     *
     * @return void
     */
    public function testCanAddHighlighting(array $fields, ?array $existingBody, string $expectedBody): void
    {
        $builder = new ClauseBuilder();

        if (! is_null($existingBody)) {
            $this->setInaccessibleProperty($builder, 'body', $existingBody);
        }

        $builder->addHighlighting($fields);

        $this->assertSame($expectedBody, json_encode($builder->getBody()));
    }

    /** @see testCanAddHighlighting */
    public function providerCanAddHighlighting(): \Generator
    {
        yield '1 field, no existing' => [
            'fields' => ['description'],
            'existingHighlights' => null,
            'expectedBody' => '{"highlight":{"fields":{"description":{}}}}',
        ];

        yield '1 field, has existing' => [
            'fields' => ['description'],
            'existingHighlights' => [
                'highlight' => [
                    'fields' => [
                        'name' => new \stdClass()
                    ],
                ],
            ],
            'expectedBody' => '{"highlight":{"fields":{"name":{},"description":{}}}}',
        ];
    }
}
