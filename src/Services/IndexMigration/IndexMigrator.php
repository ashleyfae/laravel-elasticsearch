<?php
/**
 * Reindexer.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Services\IndexMigration;

use Ashleyfae\LaravelElasticsearch\Exceptions\InvalidModelException;
use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Services\BulkDocumentReindexer;
use Ashleyfae\LaravelElasticsearch\Services\IndexManager;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\CreateNewIndex;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\SwapAlias;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps\UpdateModelVersion;
use Ashleyfae\LaravelElasticsearch\Traits\HasConsoleLogger;
use Ashleyfae\LaravelElasticsearch\Traits\StepsWithRollback;
use Exception;

/**
 * Performs zero downtime reindexing by creating a new index and adding documents to it.
 *
 * Any public properties are just to make testing easier...
 */
class IndexMigrator
{
    use HasConsoleLogger, StepsWithRollback;

    /** @var ElasticIndex model */
    protected ElasticIndex $elasticIndex;

    /** @var string index name before this update */
    public string $previousIndexName;

    /** @var string full name of the new index */
    public string $newIndexName;

    /** @var int new version of the index; used to make the new name */
    public int $newIndexVersion;

    /** @var array index mapping settings */
    public array $mapping;

    /** @var string interval before we update the settings */
    public mixed $originalRefreshInterval;

    /** @var int replica number before we updated the settings */
    public int $originalReplicas;

    public function __construct(
        protected IndexManager $indexManager,
        protected BulkDocumentReindexer $bulkDocumentReindexer
    ) {

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

    /**
     * Executes the migration.
     *
     * @return void
     * @throws InvalidModelException|Exception
     */
    public function execute(): void
    {
        try {
            $this->setIndexNames()
                ->parseMapping()
                ->createNewIndex()
                ->updateWriteAlias()
                ->addDocsToNewIndex()
                ->updateNewIndexSettings()
                ->updateReadAlias()
                ->updateModel()
                ->deleteOldIndex();
        } catch (Exception $e) {
            $this->log('ERROR - Performing rollback.');

            $this->rollbackSteps();

            $this->log('Rollback complete. Re-throwing exception.');

            throw $e;
        }
    }

    protected function setIndexNames(): static
    {
        $this->previousIndexName = $this->elasticIndex->index_name;
        $this->newIndexVersion   = $this->elasticIndex->version_number + 1;
        $this->newIndexName      = $this->elasticIndex->makeIndexNameForVersion($this->newIndexVersion);

        return $this;
    }

    protected function parseMapping(): static
    {
        $this->mapping = json_decode($this->elasticIndex->mapping, true);

        return $this;
    }

    /**
     * Creates our new index.
     *
     * @return $this
     */
    protected function createNewIndex(): static
    {
        $mapping                       = $this->mapping;
        $this->originalRefreshInterval = $mapping['settings']['refresh_interval'] ?? '60s';
        $this->originalReplicas        = $mapping['settings']['number_of_replicas'] ?? 0;

        // Override the settings for fast reindexing. We'll change them back later.
        $mapping['settings']['refresh_interval']   = 0;
        $mapping['settings']['number_of_replicas'] = 0;

        $this->log("Creating new index {$this->newIndexName}.");

        $this->addCompletedStep(
            app(CreateNewIndex::class)
                ->setMapping($mapping)
                ->setIndexName($this->newIndexName)
                ->up()
        );

        return $this;
    }

    /**
     * Moves the write alias to the new index, so that new documents are written there.
     *
     * @return $this
     */
    protected function updateWriteAlias(): static
    {
        $this->log('Swapping write alias.');

        $this->addCompletedStep(
            app(SwapAlias::class)
                ->setAliasName($this->elasticIndex->write_alias)
                ->setPreviousIndexName($this->previousIndexName)
                ->setNewIndexName($this->newIndexName)
                ->up()
        );

        return $this;
    }

    /**
     * @throws InvalidModelException
     */
    protected function addDocsToNewIndex(): static
    {
        $this->log('Beginning reindex.');

        $this->bulkDocumentReindexer
            ->forIndexableType($this->elasticIndex)
            ->when($this->hasConsole(), function (BulkDocumentReindexer $bulkDocumentReindexer) {
                $bulkDocumentReindexer->setConsole($this->command);
            })
            ->reindex();

        return $this;
    }

    protected function updateNewIndexSettings(): static
    {
        $this->log('Updating refresh interval and replicas.');

        $this->indexManager->updateIndexSettings(
            indexName: $this->newIndexName,
            body: [
                'refresh_interval'   => $this->originalRefreshInterval,
                'number_of_replicas' => $this->originalReplicas,
            ]
        );

        return $this;
    }

    protected function updateReadAlias(): static
    {
        $this->log('Swapping read alias.');

        $this->addCompletedStep(
            app(SwapAlias::class)
                ->setAliasName($this->elasticIndex->read_alias)
                ->setPreviousIndexName($this->previousIndexName)
                ->setNewIndexName($this->newIndexName)
                ->up()
        );

        return $this;
    }

    protected function updateModel(): static
    {
        $this->log('Updating index record with new version.');

        $this->addCompletedStep(
            app(UpdateModelVersion::class)
                ->setElasticIndex($this->elasticIndex)
                ->setNewVersion($this->newIndexVersion)
                ->setPreviousVersion($this->elasticIndex->version_number)
                ->up()
        );

        return $this;
    }

    protected function deleteOldIndex(): static
    {
        $this->log('Deleting old index in Elasticsearch.');

        $this->indexManager->deleteIndex($this->previousIndexName);

        return $this;
    }
}
