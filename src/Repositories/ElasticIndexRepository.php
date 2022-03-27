<?php
/**
 * ElasticIndexRepository.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Repositories;

use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;

class ElasticIndexRepository
{
    public function getIndexByIndexableType(string $type): ElasticIndex
    {
        return ElasticIndex::query()->where('indexable_type', $type)->firstOrFail();
    }
}
