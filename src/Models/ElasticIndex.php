<?php
/**
 * ElasticIndex.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Models;

use Ashleyfae\LaravelElasticsearch\Database\Factories\ElasticIndexFactory;
use Ashleyfae\LaravelElasticsearch\Exceptions\ElasticsearchMappingNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $indexable_type
 * @property int $version_number
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read string $index_name Fully qualified index name in Elasticsearch. Includes the version.
 * @property-read string $read_alias {@see ElasticIndex::getReadAliasAttribute()}
 * @property-read string $write_alias {@see ElasticIndex::getWriteAliasAttribute()}
 * @property-read string $mapping Entire mapping JSON. {@see ElasticIndex::getMappingAttribute()}
 * @property-read array $properties Property array from the mapping.
 *
 * @mixin Builder
 */
class ElasticIndex extends Model
{
    use HasFactory;

    protected $fillable = [
        'indexable_type',
        'version_number',
    ];

    protected $casts = [
        'version_number' => 'integer',
    ];

    protected static function newFactory(): ElasticIndexFactory
    {
        return ElasticIndexFactory::new();
    }

    public function makeIndexNameForVersion(int $versionNumber): string
    {
        return "{$this->indexable_type}_v{$versionNumber}";
    }

    public function getIndexNameAttribute($value): string
    {
        return $this->makeIndexNameForVersion($this->version_number);
    }

    public function getReadAliasAttribute($value): string
    {
        return $this->indexable_type.'_read';
    }

    public function getWriteAliasAttribute($value): string
    {
        return $this->indexable_type.'_write';
    }

    /**
     * Retrieves the contents of the Elasticsearch mapping.
     * Should be in: `resources/elastic-indices/{indexable_type}.json`
     *
     * @param $value
     *
     * @return string
     * @throws ElasticsearchMappingNotFoundException
     */
    public function getMappingAttribute($value): string
    {
        $possibleMappings = [
            $this->indexable_type,
        ];

        $indexMapping = false;

        foreach ($possibleMappings as $fileName) {
            $filePath = resource_path(sprintf('elastic-indices/%s.json', $fileName));
            if (file_exists($filePath)) {
                $indexMapping = file_get_contents($filePath);
                break;
            }
        }

        if (! $indexMapping) {
            throw new ElasticsearchMappingNotFoundException("Mapping not found for type: {$this->indexable_type}.");
        }

        return $indexMapping;
    }

    /**
     * Retrieves the properties array from the mapping.
     *
     * @param $value
     *
     * @return array
     * @throws ElasticsearchMappingNotFoundException
     */
    public function getPropertiesAttribute($value): array
    {
        $mapping = json_decode($this->mapping, true);

        return $mapping['mappings']['properties'] ?? [];
    }
}
