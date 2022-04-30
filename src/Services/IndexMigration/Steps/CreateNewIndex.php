<?php
/**
 * CreateNewIndex.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps;

use Ashleyfae\LaravelElasticsearch\Services\IndexManager;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Contracts\ReversibleStep;

class CreateNewIndex implements ReversibleStep
{
    protected array $mapping;
    protected string $indexName;

    public function __construct(protected IndexManager $indexManager)
    {

    }

    public function setMapping(array $mapping): static
    {
        $this->mapping = $mapping;

        return $this;
    }

    public function setIndexName(string $indexName): static
    {
        $this->indexName = $indexName;

        return $this;
    }

    public function up(): static
    {
        $this->indexManager->createIndex(
            indexName: $this->indexName,
            mapping: $this->mapping
        );

        return $this;
    }

    public function down(): void
    {
        $this->indexManager->deleteIndex($this->indexName);
    }
}
