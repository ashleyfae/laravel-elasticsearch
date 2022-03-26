<?php
/**
 * ElasticIndexFactory.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Database\Factories;

use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Illuminate\Database\Eloquent\Factories\Factory;

class ElasticIndexFactory extends Factory
{
    protected $model = ElasticIndex::class;

    public function definition(): array
    {
        return [
            'indexable_type' => 'indexable',
            'version_number' => 1,
        ];
    }
}
