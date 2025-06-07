<?php
/**
 * Reindex.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Console\Commands;

use Ashleyfae\LaravelElasticsearch\Services\BulkDocumentReindexer;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\IndexMigrator;
use Illuminate\Console\Command;

class Reindex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:reindex {model : Model alias name.} {--migrate} {--max=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates an Elasticsearch index.';

    public function __construct(protected IndexMigrator $indexMigrator, protected BulkDocumentReindexer $bulkDocumentReindexer)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        if ($this->option('migrate')) {
            $this->migrateToNewIndex();
        } else {
            $this->reindex();
        }

        $this->line('Reindex complete.');
    }

    protected function migrateToNewIndex(): void
    {
        $this->indexMigrator
            ->setConsole($this)
            ->forIndex($this->argument('model'))
            ->execute();
    }

    protected function reindex(): void
    {
        $this->bulkDocumentReindexer
            ->setConsole($this)
            ->forIndexableType($this->argument('model'))
            ->when(! empty($this->option('max')), function(BulkDocumentReindexer $reindexer) {
                $reindexer->setMax($this->option('max'));
            })
            ->reindex();
    }
}
