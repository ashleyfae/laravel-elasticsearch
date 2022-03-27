<?php
/**
 * CreateIndex.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Console\Commands;

use Ashleyfae\LaravelElasticsearch\Services\IndexManager;
use Ashleyfae\LaravelElasticsearch\Traits\IndexableModelFromAlias;
use Illuminate\Console\Command;

class CreateIndex extends Command
{
    use IndexableModelFromAlias;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:create-index {model : Model alias name.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates an Elasticsearch index.';

    public function __construct(protected IndexManager $indexManager)
    {
        parent::__construct();
    }

    /**
     * Executes the command.
     */
    public function handle()
    {
        $this->indexManager
            ->forIndexableType($this->argument('model'), false)
            ->createIndexModel();

        $this->line('Successfully created index.');
    }
}
