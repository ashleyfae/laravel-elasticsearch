<?php
/**
 * GetDocument.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Console\Commands;

use Ashleyfae\LaravelElasticsearch\Console\Commands\Traits\CanGetModelByAliasAndId;
use Ashleyfae\LaravelElasticsearch\Services\DocumentIndexer;
use Illuminate\Console\Command;

class GetDocument extends Command
{
    use CanGetModelByAliasAndId;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:get-doc {model : Model alias name.} {id : ID of the corresponding model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves a single document from Elasticsearch';

    public function __construct(protected DocumentIndexer $documentIndexer)
    {
        parent::__construct();
    }

    /**
     * Executes the command.
     */
    public function handle()
    {
        $elasticDoc = $this->documentIndexer->setModel($this->getModel())->get();

        $this->line(json_encode($elasticDoc, JSON_PRETTY_PRINT));
    }
}
