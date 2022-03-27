<?php
/**
 * GetIndex.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Console\Commands;

use Ashleyfae\LaravelElasticsearch\Repositories\ElasticIndexRepository;
use Ashleyfae\LaravelElasticsearch\Services\IndexManager;
use Ashleyfae\LaravelElasticsearch\Traits\IndexableModelFromAlias;
use Illuminate\Console\Command;

class GetIndex extends Command
{
    use IndexableModelFromAlias;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:get-index {model : Model alias name.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves an Elasticsearch index.';

    public function __construct(protected IndexManager $indexManager, protected ElasticIndexRepository $repository)
    {
        parent::__construct();
    }

    /**
     * Executes the command.
     */
    public function handle()
    {
        try {
            $index = $this->indexManager
                ->getIndex($this->repository->getIndexByIndexableType($this->argument('model'))->index_name);

            dump($index);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
