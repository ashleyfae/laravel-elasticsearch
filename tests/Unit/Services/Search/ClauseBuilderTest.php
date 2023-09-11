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
     * @param  object[]  $fields
     * @param array $highlightSettings
     * @param array|null $existingBody
     * @param  string  $expectedBody
     *
     * @return void
     */
    public function testCanAddHighlighting(array $fields, array $highlightSettings, ?array $existingBody, string $expectedBody): void
    {
        $builder = new ClauseBuilder();

        if (! is_null($existingBody)) {
            $this->setInaccessibleProperty($builder, 'body', $existingBody);
        }

        $builder->addHighlighting($fields, $highlightSettings);

        $this->assertSame($expectedBody, json_encode($builder->getBody()));
    }

    /** @see testCanAddHighlighting */
    public function providerCanAddHighlighting(): \Generator
    {
        yield '1 field, no existing, no settings' => [
            'fields' => ['description' => new \stdClass()],
            'highlightSettings' => [],
            'existingHighlights' => null,
            'expectedBody' => '{"highlight":{"fields":{"description":{}}}}',
        ];

        yield '1 field, no existing, empty array converted to object' => [
            'fields' => ['description' => []],
            'highlightSettings' => [],
            'existingHighlights' => null,
            'expectedBody' => '{"highlight":{"fields":{"description":{}}}}',
        ];

        yield '1 field, has existing, no settings' => [
            'fields' => ['description' => new \stdClass()],
            'highlightSettings' => [],
            'existingHighlights' => [
                'highlight' => [
                    'fields' => [
                        'name' => new \stdClass()
                    ],
                ],
            ],
            'expectedBody' => '{"highlight":{"fields":{"name":{},"description":{}}}}',
        ];

        yield '1 field, no existing, has field settings' => [
            'fields' => ['description' => ['number_of_fragments' => 0]],
            'highlightSettings' => [],
            'existingHighlights' => null,
            'expectedBody' => '{"highlight":{"fields":{"description":{"number_of_fragments":0}}}}',
        ];

        yield '1 field, no existing, has field settings, has highlight settings' => [
            'fields' => ['description' => ['number_of_fragments' => 0]],
            'highlightSettings' => [
                'pre_tags' => ['<mark>'],
            ],
            'existingHighlights' => null,
            'expectedBody' => '{"highlight":{"pre_tags":["<mark>"],"fields":{"description":{"number_of_fragments":0}}}}',
        ];
    }
}
