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
use Ashleyfae\LaravelElasticsearch\Tests\Models\IndexableModel;
use Ashleyfae\LaravelElasticsearch\Traits\ValidatesIndexableModels;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class CreateIndex extends Command
{
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
        try {
            $class = Relation::getMorphedModel($this->argument('model'));
            if (is_null($class) || ! class_exists($class)) {
                throw new \Exception('Model not found.');
            }

            /** @var Model&IndexableModel $model */
            $model = new $class;

            $this->indexManager->forModel($model)->createIndex();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
