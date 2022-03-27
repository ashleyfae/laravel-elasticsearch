<?php
/**
 * DeleteIndex.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Console\Commands;

use Ashleyfae\LaravelElasticsearch\Repositories\ElasticIndexRepository;
use Ashleyfae\LaravelElasticsearch\Services\IndexManager;
use Illuminate\Console\Command;

class DeleteIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:delete-index {model : Model alias name.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes an Elasticsearch index.';

    public function __construct(protected IndexManager $indexManager, protected ElasticIndexRepository $repository)
    {
        parent::__construct();
    }

    /**
     * Executes the command.
     */
    public function handle()
    {
        $this->repository->getIndexByIndexableType($this->argument('model'))->delete();

        $this->line('Index deleted successfully.');
    }
}
