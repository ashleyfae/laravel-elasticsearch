<?php
/**
 * Reindexer.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Services;

use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class Reindexer
{
    /** @var ElasticIndex model */
    protected ElasticIndex $elasticIndex;

    /** @var Command console command, for writing output */
    protected Command $command;

    /** @var string index name before this update */
    protected string $previousIndexName;

    /** @var string full name of the new index */
    protected string $newIndexName;

    /** @var array index mapping settings */
    protected array $mapping;

    /** @var string interval before we update the settings */
    protected mixed $originalRefreshInterval;

    /** @var int replica number before we updated the settings */
    protected int $originalReplicas;

    public function __construct(protected IndexManager $indexManager)
    {

    }

    /**
     * Sets the ElasticIndex.
     *
     * @param  string|ElasticIndex  $indexableType  Model alias name or index object.
     *
     * @return $this
     */
    public function forIndex(string|ElasticIndex $indexableType): static
    {
        if ($indexableType instanceof ElasticIndex) {
            $this->elasticIndex = $indexableType;
        } else {
            $this->elasticIndex = ElasticIndex::query()
                ->where('indexable_type', $indexableType)
                ->firstOrFail();
        }

        return $this;
    }

    public function setConsole(Command $command): static
    {
        $this->command = $command;

        return $this;
    }

    public function execute(): void
    {
        $this->setIndexNames()
            ->parseMapping()
            ->createNewIndex()
            ->updateWriteAlias()
            ->addDocsToNewIndex()
            ->updateNewIndexSettings()
            ->updateReadAlias()
            ->updateModel()
            ->deleteOldIndex();
    }

    protected function log(string $message): void
    {
        if (isset($this->command)) {
            $this->command->line($message);
        }
    }

    protected function setIndexNames(): static
    {
        $this->previousIndexName = $this->elasticIndex->index_name;
        $this->newIndexName      = $this->elasticIndex->makeIndexNameForVersion($this->elasticIndex->version_number + 1);

        return $this;
    }

    protected function parseMapping(): static
    {
        $this->mapping = json_decode($this->elasticIndex->mapping, true);

        return $this;
    }

    protected function createNewIndex(): static
    {
        $mapping = $this->mapping;

        // Override the settings for fast reindexing.
        $mapping['settings']['refresh_interval']   = 0;
        $mapping['settings']['number_of_replicas'] = 0;

        $this->log("Creating new index {$this->newIndexName}.");

        $this->indexManager->createIndex(
            indexName: $this->newIndexName,
            mapping: $mapping
        );

        return $this;
    }

    protected function updateWriteAlias(): static
    {
        $this->log('Swapping write alias.');

        $this->indexManager->swapAlias(
            alias: $this->elasticIndex->read_alias,
            removeAliasFrom: $this->previousIndexName,
            addAliasTo: $this->newIndexName
        );

        return $this;
    }
}
